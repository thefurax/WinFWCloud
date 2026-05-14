#!/bin/bash
# Script de démarrage rapide pour le développement

echo "Vérification des dépendances backend..."
if [ ! -d "backend/vendor" ]; then
    echo "Dossier vendor manquant. Installation des dépendances Symfony..."
    cd backend && composer install && cd ..
fi

echo "Démarrage de l'infrastructure Docker..."
docker-compose up -d

echo "Application des migrations base de données..."
cd backend && php bin/console doctrine:migrations:migrate --no-interaction && cd ..

echo "Lancement du serveur Symfony..."
cd backend && php -S localhost:8000 -t public
