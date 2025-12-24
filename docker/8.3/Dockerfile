FROM ubuntu:24.04

LABEL maintainer="Pakawa K."

ARG WWWGROUP
ARG WWWUSER
ARG NODE_VERSION=22

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
# Section 1: Timezone set to Asia/Bangkok
ENV TZ=Asia/Bangkok
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

# Section 2: Core Dependencies & Focused PHP 8.3 (PostgreSQL focus)
RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano \
    # PHP Repository Keys
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor >> /etc/apt/keyrings/ppa_ondrej_php.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    # Postgres Repository Keys
    && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    # Node.js Repository Setup
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    # Now run update ONCE and install everything
    && apt-get update \
    && apt-get install -y nodejs libgd3 php8.3-cli php8.3-dev php8.3-common \
       php8.3-pgsql php8.3-sqlite3 php8.3-gd \
       php8.3-curl php8.3-imap php8.3-mysql php8.3-mbstring \
       php8.3-xml php8.3-zip php8.3-bcmath php8.3-soap \
       php8.3-intl php8.3-readline php8.3-msgpack \
       php8.3-igbinary php8.3-redis php8.3-pcov php8.3-imagick \
       php8.3-xdebug postgresql-client \
    && npm install -g npm \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    # Cleanup to keep image small
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc \
    && curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | tee /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql17 odbcinst=2.3.7 odbcinst1debian2=2.3.7 unixodbc-dev=2.3.7 unixodbc=2.3.7
RUN pecl install sqlsrv \
    && pecl install pdo_sqlsrv \
    && echo "extension=sqlsrv.so" > /etc/php/8.3/mods-available/sqlsrv.ini \
    && echo "extension=pdo_sqlsrv.so" > /etc/php/8.3/mods-available/pdo_sqlsrv.ini \
    && phpenmod -v 8.3 sqlsrv pdo_sqlsrv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Section 3: User & Permissions
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.3
# Rename or Create Group
RUN if getent group ${WWWGROUP}; then \
        groupmod -n sail $(getent group ${WWWGROUP} | cut -d: -f1); \
    else \
        groupadd --force -g ${WWWGROUP} sail; \
    fi
# Rename or Create User
RUN if getent passwd ${WWWUSER}; then \
        usermod -l sail $(getent passwd ${WWWUSER} | cut -d: -f1); \
        usermod -d /home/sail -m sail; \
    else \
        useradd -ms /bin/bash --no-user-group -g ${WWWGROUP} -u ${WWWUSER} sail; \
    fi
RUN mkdir -p /home/sail/.composer && chown -R ${WWWUSER}:${WWWGROUP} /home/sail

# Section 4: Project Files & Supervisor
COPY . /var/www/html

# Combined permissions into one RUN block for efficiency
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R ${WWWUSER}:${WWWGROUP} /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.3/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp 8080/tcp

ENTRYPOINT ["start-container"]