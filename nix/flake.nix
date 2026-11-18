{
  inputs = {
    nixpkgs.url = "github:NixOs/nixpkgs/nixpkgs-unstable";
    systems.url = "github:nix-systems/default";
    # Using devenv.sh for development environments
    devenv = {
      url = "github:cachix/devenv";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };
  outputs =
    {
      self,
      nixpkgs,
      devenv,
      systems,
      ...
    }@inputs:
    let
      # Use to iterate over all supported architectures
      forEachSystem = nixpkgs.lib.genAttrs (import systems);
      # Helper function to get environment variables with a default value
      getEnvDefault =
        name: default:
        let
          value = builtins.getEnv name;
        in
        if value == "" then default else value;
    in
    {
      # Configure development environments for each system (run `nix develop path:nix --impure` from project root to enter)
      devShells = forEachSystem (
        system:
        let
          pkgs = nixpkgs.legacyPackages.${system};
          # To configure your environment, set any env variables in .env.development.local
          # which is loaded via direnv (see .envrc) into your shell so that nix flake can read them.
          FPMPort = getEnvDefault "LOCAL_PHP_FPM_PORT" "3030";
          ServerName = getEnvDefault "LOCAL_VHOST_SERVER_NAME" "wp.test";
        in
        {
          default = devenv.lib.mkShell {
            inherit inputs pkgs;
            modules = [
              (
                { pkgs, config, ... }:
                # This is your devenv configuration
                {
                  packages = [
                    # PHP
                    pkgs.intelephense
                    pkgs.phpactor
                    pkgs.wp-cli
                    # Tools
                    pkgs.vscode-langservers-extracted # For HTML, CSS, JSON, and other languages
                    pkgs.emmet-ls # Emmet support for HTML and CSS
                  ];

                  # MariaDB / MySQL service configuration
                  services.mysql = {
                    enable = true;
                    initialDatabases = [ { name = getEnvDefault "DB_NAME" "wp"; } ];
                    settings = {
                      mysqld = {
                        # TODO Pick a DB port that's unique to this project (e.g. 3351) so its MariaDB
                        # can run alongside other projects' dev servers. Set it as the default here and
                        # as DB_PORT in .env.development (the app reads it there), or override both with
                        # DB_PORT in .env.development.local.
                        port = builtins.fromJSON (getEnvDefault "DB_PORT" "3306");
                      };
                    };
                  };

                  # PHP configuration
                  languages.php = {
                    enable = true;
                    version = "8.5";
                    extensions = [
                      "xdebug"
                      "imagick"
                    ];
                    ini = ''
                      memory_limit=256M
                      log_errors_max_len=0
                      error_log=${config.env.DEVENV_STATE}/php/error.log
                      log_errors=1
                      error_reporting=E_ALL | E_STRICT
                      display_errors=1
                      date.timezone=America/Los_Angeles
                      xdebug.mode=debug
                      xdebug.max_nesting_level=512
                      upload_max_filesize = 32M
                      post_max_size = 64M
                    '';
                    fpm = {
                      settings = {
                        "error_log" = (config.env.DEVENV_STATE + "/php-fpm/error.log");
                        "log_level" = "alert";
                      };
                      pools.wp = {
                        listen = "127.0.0.1:${FPMPort}";
                        settings = {
                          "listen.allowed_clients" = "127.0.0.1";
                          "listen.mode" = "0660";
                          "pm" = "dynamic";
                          "pm.max_children" = 50;
                          "pm.start_servers" = 2;
                          "pm.min_spare_servers" = 1;
                          "pm.max_spare_servers" = 5;
                          "pm.process_idle_timeout" = 30;
                          "pm.max_requests" = 500;
                          "pm.status_path" = "/status";
                          "ping.path" = "/ping";
                          "ping.response" = "pong";
                          "request_terminate_timeout" = 0;
                          "slowlog" = config.env.DEVENV_STATE + "/php-fpm/slow.log";
                          "security.limit_extensions" = ".php .php5";
                          "access.log" = config.env.DEVENV_STATE + "/php-fpm/access.log";
                          "catch_workers_output" = "yes";
                        };
                      };
                    };
                  };

                  process.manager.before = # bash
                    ''
                      if [ ! -f "${config.env.DEVENV_STATE}/php/error.log" ]; then
                        mkdir -p "${config.env.DEVENV_STATE}/php"
                        touch "${config.env.DEVENV_STATE}/php/error.log"
                        echo "PHP error log initialized at ${config.env.DEVENV_STATE}/php/error.log"
                      fi

                      trap down SIGHUP EXIT
                    '';
                  process.manager.after = # bash
                    ''
                      if [ -f "${config.env.DEVENV_STATE}/php/error.log" ]; then
                        rm "${config.env.DEVENV_STATE}/php/error.log"
                      fi
                    '';

                  processes = {
                    php-error-logs.exec = "tail -f -n0 '${config.env.DEVENV_STATE}/php/error.log'";
                  }
                  // (
                    if config.services.nginx.enable then
                      {
                        nginx-access-logs.exec = "tail -f -n0 '${config.env.DEVENV_STATE}/nginx/access.log'";
                        nginx-error-logs.exec = "tail -f -n0 '${config.env.DEVENV_STATE}/nginx/error.log'";
                      }
                    else
                      { }
                  );

                  # Nginx configuration
                  # Nginx serves plain HTTP only. HTTPS and the *.test domain come from a Caddy reverse
                  # proxy in front of it, so there are no certificates or HTTPS server here.
                  services.nginx = {
                    enable = true;
                    httpConfig = # conf
                      let
                        DocumentRoot = (config.env.DEVENV_ROOT + "/public");
                        # TODO Pick an Nginx port that's unique to this project (e.g. 8006), set it as the
                        # default here (or LOCAL_NGINX_PORT in .env.development.local), then add the site
                        # to your Caddyfile so Caddy proxies its *.test domain (the WP_HOME host in
                        # .env.development) to this port with a local certificate:
                        #
                        #   wp.test {
                        #     tls internal
                        #     reverse_proxy 127.0.0.1:8080
                        #   }
                        #
                        # The *.test name must resolve to 127.0.0.1 (/etc/hosts or dnsmasq). Caddy sends
                        # X-Forwarded-Proto, which config/app.php uses to detect HTTPS.
                        NginxPort = getEnvDefault "LOCAL_NGINX_PORT" "8080";
                      in
                      ''
                        keepalive_timeout  65;

                        # HTTP server
                        server {
                            listen       ${NginxPort};
                            server_name  ${ServerName};
                            root ${DocumentRoot};
                            access_log ${config.env.DEVENV_STATE}/nginx/access.log;
                            error_log  ${config.env.DEVENV_STATE}/nginx/error.log error;

                            index index.php index.htm index.html;

                            # Do not preserve port in redirects to avoid issues with non-standard ports
                            # while behind a reverse proxy or load balancer.
                            port_in_redirect off;

                            # Prevent PHP scripts from being executed inside the uploads folder.
                            location ~* /content/uploads/.*.php$ {
                              deny all;
                            }
                            location / {
                              try_files $uri $uri/ /index.php?$args;
                            }

                            location ~ \.php$ {
                              try_files $uri =404;
                              fastcgi_pass 127.0.0.1:${FPMPort};
                              fastcgi_index index.php;
                              include ${pkgs.nginx}/conf/fastcgi_params;
                              fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                              fastcgi_param SERVER_PORT $server_port;
                              fastcgi_intercept_errors on;
                            }
                        }
                      '';
                  };
                }
              )
            ]
            ++ (
              if pkgs.stdenv.hostPlatform.isDarwin then
                [
                  # Uncomment the following line to enable Apache virtual host configuration on macOS
                  # It copies a pre-defined vhost configuration file to your Apache config directory
                  # to be used with the built-in Apache server on macOS.
                  # If you enable this, make sure to adjust your /etc/hosts file accordingly.
                  # Also disable Nginx service above to avoid port conflicts.
                  #
                  # (import ./mac-apache-vhost.nix {
                  #   inherit FPMPort ServerName;
                  # })
                ]
              else
                [ ]
            );
          };
        }
      );
    };
}
