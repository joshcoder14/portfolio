
#!/bin/bash
set -e # Exit immediately if a command exits with a non-zero status

source ~/.nvm/nvm.sh;
cd /var/www/html/wp-content/themes/fivehundred-child

echo "======================================="
echo "|     Starting Pulling Repository     |"
echo "======================================="
git pull origin main
echo "======================================="
echo "|     Done Pulling Repository         |"
echo "======================================="

echo "======================================="
echo "|     Starting Building Assets        |"
echo "======================================="
nvm use 20
npm run build
echo "======================================="
echo "|     Done Building Assets            |"
echo "======================================="