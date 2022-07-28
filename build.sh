#############################
#  ONLY FOR USE ON DEV !!!! #
#############################

# Stop Docker
./vendor/bin/sail down

# Remove existing docker images and volumes
docker rm -f $(docker ps -a -q)
docker volume rm $(docker volume ls -q)

# Rebuild the images
./vendor/bin/sail build --no-cache

# Run Docker
./vendor/bin/sail up -d

# Migrate the database with seeding
./vendor/bin/sail artisan migrate:fresh --seed
