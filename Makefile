APP_NAME=pos-textile
DOCKER_COMPOSE=docker-compose
GREEN=\033[0;32m
YELLOW=\033[1;33m
NC=\033[0m

.PHONY: help build up down logs restart clean deploy migrate seed

help:
	@echo "$(GREEN)Available commands:$(NC)"
	@echo "  $(YELLOW)make build$(NC)     - Build Docker image"
	@echo "  $(YELLOW)make up$(NC)        - Start services"
	@echo "  $(YELLOW)make down$(NC)      - Stop services"
	@echo "  $(YELLOW)make logs$(NC)      - View logs"
	@echo "  $(YELLOW)make restart$(NC)   - Restart services"
	@echo "  $(YELLOW)make deploy$(NC)    - Full deploy (build + up)"
	@echo "  $(YELLOW)make clean$(NC)     - Remove containers and volumes"

build:
	@echo "$(GREEN)Building Docker image...$(NC)"
	$(DOCKER_COMPOSE) build

up:
	@echo "$(GREEN)Starting services...$(NC)"
	$(DOCKER_COMPOSE) up -d

down:
	@echo "$(YELLOW)Stopping services...$(NC)"
	$(DOCKER_COMPOSE) down

logs:
	$(DOCKER_COMPOSE) logs -f

restart:
	$(DOCKER_COMPOSE) restart

clean:
	$(DOCKER_COMPOSE) down -v
	docker system prune -f

migrate:
	@echo "$(GREEN)Running migrations...$(NC)"
	$(DOCKER_COMPOSE) exec app php artisan migrate --force

seed:
	@echo "$(GREEN)Running seeders...$(NC)"
	$(DOCKER_COMPOSE) exec app php artisan db:seed --force

deploy:
	@echo "$(GREEN)Deploying to production...$(NC)"
	docker rm -f pos-textile-app || true
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)Deployment complete!$(NC)"