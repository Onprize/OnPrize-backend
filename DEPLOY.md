# On-Prize Backend - Laravel

## Deploy to Render

### Prerequisites
- GitHub repository
- TiDB Cloud database

### Environment Variables

Set these in Render dashboard:
- `APP_KEY`: Generate with `php artisan key:generate --show`
- `APP_URL`: Your Render app URL
- `DB_HOST`: TiDB host
- `DB_USERNAME`: TiDB username
- `DB_PASSWORD`: TiDB password

### Deploy Steps

1. Connect GitHub repository to Render
2. Create new Web Service
3. Select Docker runtime
4. Set environment variables
5. Deploy
