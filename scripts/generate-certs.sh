#!/bin/bash
# Génération de certificats pour mTLS (MVP)

mkdir -p certs
cd certs

echo "1. Génération de la CA..."
openssl genrsa -out ca.key 4096
openssl req -new -x509 -days 365 -key ca.key -out ca.crt -subj "/CN=FirewallCentralCA"

echo "2. Génération du certificat Serveur..."
openssl genrsa -out server.key 2048
openssl req -new -key server.key -out server.csr -subj "/CN=localhost"
openssl x509 -req -days 365 -in server.csr -CA ca.crt -CAkey ca.key -set_serial 01 -out server.crt

echo "3. Génération du certificat Agent..."
openssl genrsa -out agent.key 2048
openssl req -new -key agent.key -out agent.csr -subj "/CN=agent-001"
openssl x509 -req -days 365 -in agent.csr -CA ca.crt -CAkey ca.key -set_serial 02 -out agent.crt

echo "Terminé. Certificats générés dans ./certs"
