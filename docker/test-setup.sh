#!/bin/bash
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Create Uploads folder and chown it for web user
docker-compose run wordpress mkdir -p /var/www/html/wp-content/uploads
docker-compose run wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads/

# Perform WP installation process.
${__dir}/wp.sh core install \
    --url="127.0.0.1:8000" \
    --title="DynamicContentManager" \
    --admin_user="test" \
    --admin_email="test@wordpress.test" \
    --admin_password="test"

# Change default tagline.
${__dir}/wp.sh option update blogdescription "A starter WordPress environment built with Docker."
${__dir}/wp.sh option update permalink_structure '/%postname%'

# Activate plugin to be developed
${__dir}/wp.sh plugin activate woda-dynamic-content-manager

# Turn on debugging.
${__dir}/wp.sh config set WP_DEBUG true --raw --type="constant"
${__dir}/wp.sh config set WP_DEBUG_LOG true --raw --type="constant"

# Remove all posts, comments, and terms.
${__dir}/wp.sh site empty --yes

# Remove default plugins and themes.
${__dir}/wp.sh plugin delete hello-dolly
${__dir}/wp.sh plugin delete akismet
#${__dir}/wp.sh theme delete twentyfifteen
#${__dir}/wp.sh theme delete twentysixteen

# Remove widgets.
#${__dir}/wp.sh widget delete recent-posts-2
#${__dir}/wp.sh widget delete recent-comments-2
#${__dir}/wp.sh widget delete archives-2
#${__dir}/wp.sh widget delete search-2
#${__dir}/wp.sh widget delete categories-2
#${__dir}/wp.sh widget delete meta-2

