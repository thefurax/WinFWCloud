#!/bin/bash
# Script de démarrage rapide pour le développement

echo "Démarrage de l'infrastructure Docker..."
docker-compose up -d

echo "Installation des dépendances backend..."
cd backend && composer install

echo "Initialisation de la base de données..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Lancement du serveur Symfony..."
php -S localhost:8000 -t public
