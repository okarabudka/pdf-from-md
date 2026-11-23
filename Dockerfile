# PHP CLI with pandoc and XeLaTeX, so nothing has to be installed on the host.
FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        pandoc texlive-xetex texlive-fonts-recommended texlive-latex-recommended lmodern unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
