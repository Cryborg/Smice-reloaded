#############################
#  ONLY FOR USE ON DEV !!!! #
#############################

# Remove existing docker images and volumes
docker rm -f $(docker ps -a -q)
docker volume rm $(docker volume ls -q)

# Rebuild the images
sail build --no-cache

# Run Docker
sail up -d

# Migrate the database with seeding
sail artisan migrate:fresh --seed
