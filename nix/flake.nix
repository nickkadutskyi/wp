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
        in
        {
          default = devenv.lib.mkShell {
            inherit inputs pkgs;
            modules = [
              (
                { pkgs, config, ... }:
                # This is your devenv configuration
                let
                  FPMPort = getEnvDefault "LOCAL_PHP_FPM_PORT" "3030";
                in
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
                    initialDatabases = [ { name = getEnvDefault "LOCAL_MYSQAL_DB_NAME" "wp"; } ];
                    settings = {
                      mysqld = {
                        port = getEnvDefault "LOCAL_MYSQL_PORT" "3310";
                      };
                    };
                  };
                  # PHP configuration
                  languages.php = {
                    enable = true;
                    version = "8.4";
                    extensions = [ "xdebug" "imagick" ];
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
                  processes = {
                    php-error-logs.exec = "tail -f -n0 '${config.env.DEVENV_STATE}/php/error.log'";
                  };
                }
              )
            ];
          };
        }
      );
    };
}
