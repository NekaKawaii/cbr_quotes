DC_DEV := docker-compose -f docker-compose.yml -f docker-compose.dev.yml

dev@up:
	$(DC_DEV) up -d

dev@build:
	$(DC_DEV) build
