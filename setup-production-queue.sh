#!/bin/bash

echo "=========================================="
echo "AneWP Laravel - Production Queue Setup"
echo "=========================================="
echo ""

# Check if running on macOS or Linux
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Detected: macOS"
    PLATFORM="macos"
else
    echo "Detected: Linux"
    PLATFORM="linux"
fi

echo ""
echo "This script will setup:"
echo "1. Cron job for Laravel Scheduler (auto-check every 15 min)"
echo "2. Queue worker that runs in background"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Setup cancelled."
    exit 1
fi

# Get project path
PROJECT_PATH=$(pwd)
echo ""
echo "Project path: $PROJECT_PATH"

# Setup 1: Cron Job for Scheduler
echo ""
echo "=========================================="
echo "Step 1: Setup Cron Job"
echo "=========================================="
echo ""

CRON_COMMAND="* * * * * cd $PROJECT_PATH && /usr/bin/php artisan schedule:run >> /dev/null 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "✓ Cron job already exists"
else
    echo "Adding cron job..."
    (crontab -l 2>/dev/null; echo "$CRON_COMMAND") | crontab -
    echo "✓ Cron job added successfully"
fi

echo ""
echo "Cron job installed:"
echo "$CRON_COMMAND"

# Setup 2: Queue Worker
echo ""
echo "=========================================="
echo "Step 2: Setup Queue Worker"
echo "=========================================="
echo ""

if [[ "$PLATFORM" == "macos" ]]; then
    # macOS - Use LaunchAgent
    PLIST_FILE="$HOME/Library/LaunchAgents/com.anewplaravel.queue.plist"

    echo "Creating LaunchAgent plist file..."

    cat > "$PLIST_FILE" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.anewplaravel.queue</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/php</string>
        <string>$PROJECT_PATH/artisan</string>
        <string>queue:work</string>
        <string>--sleep=3</string>
        <string>--tries=3</string>
        <string>--max-time=3600</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
    <key>StandardOutPath</key>
    <string>$PROJECT_PATH/storage/logs/queue-worker.log</string>
    <key>StandardErrorPath</key>
    <string>$PROJECT_PATH/storage/logs/queue-worker-error.log</string>
</dict>
</plist>
EOF

    echo "✓ LaunchAgent file created: $PLIST_FILE"

    # Load the LaunchAgent
    launchctl unload "$PLIST_FILE" 2>/dev/null
    launchctl load "$PLIST_FILE"

    echo "✓ Queue worker started via LaunchAgent"
    echo ""
    echo "To check status:"
    echo "  launchctl list | grep anewplaravel"
    echo ""
    echo "To stop:"
    echo "  launchctl unload $PLIST_FILE"
    echo ""
    echo "To start:"
    echo "  launchctl load $PLIST_FILE"

else
    # Linux - Provide systemd instructions
    echo "Creating systemd service file template..."

    cat > "$PROJECT_PATH/anewplaravel-queue.service" << EOF
[Unit]
Description=AneWP Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$PROJECT_PATH
ExecStart=/usr/bin/php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5s

StandardOutput=append:$PROJECT_PATH/storage/logs/queue-worker.log
StandardError=append:$PROJECT_PATH/storage/logs/queue-worker-error.log

[Install]
WantedBy=multi-user.target
EOF

    echo "✓ Systemd service file created: $PROJECT_PATH/anewplaravel-queue.service"
    echo ""
    echo "To install and start the service, run:"
    echo ""
    echo "  sudo cp $PROJECT_PATH/anewplaravel-queue.service /etc/systemd/system/"
    echo "  sudo systemctl daemon-reload"
    echo "  sudo systemctl enable anewplaravel-queue"
    echo "  sudo systemctl start anewplaravel-queue"
    echo ""
    echo "To check status:"
    echo "  sudo systemctl status anewplaravel-queue"
fi

# Summary
echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "✓ Scheduler: Runs every minute (checks for jobs every 15 min)"
echo "✓ Queue Worker: Running in background"
echo ""
echo "How it works:"
echo "1. Every 15 minutes, scheduler dispatches jobs to check WordPress post status"
echo "2. Queue worker processes jobs in background (non-blocking)"
echo "3. When WordPress publishes a scheduled post, status auto-updates in your app"
echo ""
echo "Logs:"
echo "  - Queue worker: $PROJECT_PATH/storage/logs/queue-worker.log"
echo "  - Queue errors: $PROJECT_PATH/storage/logs/queue-worker-error.log"
echo "  - Laravel app: $PROJECT_PATH/storage/logs/laravel.log"
echo ""
echo "Test the system:"
echo "  php artisan wordpress:sync-status"
echo ""
