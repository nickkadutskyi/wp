{ FPMPort, ServerName }:
{
  pkgs,
  config,
  ...
}:
let
  # Helper function to get environment variables with a default value
  getEnvDefault =
    name: default:
    let
      value = builtins.getEnv name;
    in
    if value == "" then default else value;
  ApacheConfig = getEnvDefault "LOCAL_APACHE_CONFIG" "/etc/apache2/other";
  DocumentRoot = (config.env.DEVENV_ROOT + "/public");

  mkVhostFile =
    isSslEnabled:
    pkgs.writeText (ServerName + ".conf") ''
      <VirtualHost ${if isSslEnabled then ''*:443'' else ''*:80''}>
          DocumentRoot ${DocumentRoot}
          ServerName ${ServerName}
          ServerAlias ${ServerName}* www.${ServerName}
          ErrorLog "/private/var/log/apache2/${ServerName}-error_log"
          CustomLog "/private/var/log/apache2/${ServerName}-access_log" common
          SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1

          ${
            if isSslEnabled then
              ''
                # SSL Configuration
                      SSLEngine on
                      SSLCertificateFile ${config.env.DEVENV_STATE}/mkcert/${ServerName}.pem
                      SSLCertificateKeyFile ${config.env.DEVENV_STATE}/mkcert/${ServerName}-key.pem
                      SSLCertificateChainFile ${config.env.DEVENV_STATE}/mkcert/rootCA.pem''
            else
              ''# SSL is enabled in ${ServerName}-ssl.conf file''
          }

          <FilesMatch "\.php$">
            SetHandler "proxy:fcgi://127.0.0.1:${FPMPort}"
          </FilesMatch>

          <Directory "${DocumentRoot}">
            AllowOverride All
            Options -Indexes +FollowSymLinks

            <IfModule authz_host_module>
              Require all granted
            </IfModule>

            # BEGIN WordPress
            <IfModule mod_rewrite.c>
              RewriteEngine On
              RewriteBase /
              RewriteRule ^index.php$ - [L]
              RewriteCond %{REQUEST_FILENAME} !-f
              RewriteCond %{REQUEST_FILENAME} !-d
              RewriteRule . /index.php [L]
            </IfModule>
            # END WordPress
          </Directory>
      </VirtualHost>

      ${
        if !isSslEnabled then
          ''
            # Enable 'status' and 'ping' pages for monitoring php-fpm
            <LocationMatch "/(ping|status)">
                SetHandler "proxy:fcgi://127.0.0.1:${FPMPort}"
            </LocationMatch>
          ''
        else
          ''# /ping and /status are enabled in ${ServerName}.conf file''
      }
    '';

  vhostConfig = mkVhostFile false;
  vhostConfigSSL = mkVhostFile true;
in
{
  certificates = [
    ServerName
  ];
  process.manager.before = # bash
    ''
      APACHE_CONF="${ApacheConfig}/${ServerName}.conf"
      APACHE_CONF_SSL="${ApacheConfig}/${ServerName}-ssl.conf"
      DEVENV_CONF="${vhostConfig}"
      DEVENV_CONF_SSL="${vhostConfigSSL}"

      if ! cmp -s "$APACHE_CONFIG" "$DEVENV_CONF"; then
        echo "Configuring Vhost for ${ServerName}"
        sudo cp -f "$DEVENV_CONF" "$APACHE_CONF"
        sudo cp -f "$DEVENV_CONF_SSL" "$APACHE_CONF_SSL"
        sudo apachectl restart
      else
        echo "Vhost is already configured"
      fi
      if [ ! -f "/var/log/apache2/${ServerName}-access_log" ]; then
        sudo touch "/var/log/apache2/${ServerName}-access_log"
      fi
      if [ ! -f "/var/log/apache2/${ServerName}-error_log" ]; then
        sudo touch "/var/log/apache2/${ServerName}-error_log"
      fi

      trap down SIGHUP EXIT
    '';
  process.manager.after = # bash
    ''
      sudo rm ${ApacheConfig}/${ServerName}.conf
      sudo rm ${ApacheConfig}/${ServerName}-ssl.conf
      sudo apachectl restart
    '';
  processes = {
    apache-access-logs.exec = "tail -f -n0 '/var/log/apache2/${ServerName}-access_log'";
    apache-error-logs.exec = "tail -f -n0 '/var/log/apache2/${ServerName}-error_log'";
  };

}
