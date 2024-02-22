# LEGO manuals
Based on SQLite database and a bunch of pdf files. Both located on "public/data".\
This directory is also mounted to docker environment.

## Development
Start development environment:
1) goto /docker
2) run `docker-compose -f docker-compose.yaml -f docker-compose.dev.yaml up -d`

## Build multi-arch image
    docker buildx create --name mybuilder
    docker buildx use mybuilder
    docker buildx build --platform linux/amd64,linux/arm/v7 --tag ojooss/cooking:latest --push  .
