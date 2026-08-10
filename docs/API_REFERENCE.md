# Kravyo API Reference

> This file is a placeholder for future API documentation if the platform expands to include mobile applications (React Native, Flutter) or third-party integrations.

Currently, the Kravyo platform is a monolithic MVC web application. API endpoints will be documented here if a RESTful or GraphQL API is developed.

## Planned API Endpoints (Future Scope)

### Authentication
* `POST /api/auth/login`
* `POST /api/auth/register`

### Customers
* `GET /api/kitchens`
* `GET /api/kitchens/{id}/menu`
* `POST /api/orders/checkout`
* `GET /api/orders/{id}/track`

### Home Chefs
* `GET /api/chef/orders`
* `POST /api/chef/orders/{id}/status`
* `PUT /api/chef/menu/{id}`

### Zero Food Waste
* `GET /api/zero-waste/deals`
