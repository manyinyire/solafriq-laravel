# Deploy Assets to Production Server
# This script helps you deploy the built Vite assets to your production server

Write-Host "🚀 SolaFriq Asset Deployment Script" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$localBuildPath = "public\build"
$serverUser = "YOUR_SSH_USERNAME"  # Replace with your SSH username
$serverHost = "solafriq.com"       # Your server hostname
$serverPath = "/home/solafriq/domains/solafriq.com/app/public/build"

Write-Host "📋 Deployment Configuration:" -ForegroundColor Yellow
Write-Host "   Local Path: $localBuildPath"
Write-Host "   Server: $serverUser@$serverHost"
Write-Host "   Remote Path: $serverPath"
Write-Host ""

# Check if build directory exists
if (-not (Test-Path $localBuildPath)) {
    Write-Host "❌ Error: Build directory not found!" -ForegroundColor Red
    Write-Host "   Please run 'npm run build' first." -ForegroundColor Red
    exit 1
}

# Check if manifest.json exists
if (-not (Test-Path "$localBuildPath\manifest.json")) {
    Write-Host "❌ Error: manifest.json not found!" -ForegroundColor Red
    Write-Host "   Please run 'npm run build' first." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Build files verified locally" -ForegroundColor Green
Write-Host ""

# Display deployment options
Write-Host "📤 Deployment Options:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Deploy via SCP (requires SSH access)" -ForegroundColor White
Write-Host "2. Deploy via rsync (requires rsync + SSH)" -ForegroundColor White
Write-Host "3. Show manual FTP instructions" -ForegroundColor White
Write-Host "4. Create ZIP file for manual upload" -ForegroundColor White
Write-Host "5. Exit" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Select an option (1-5)"

switch ($choice) {
    "1" {
        Write-Host ""
        Write-Host "📦 Deploying via SCP..." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Run this command in your terminal:" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "scp -r $localBuildPath $serverUser@${serverHost}:$serverPath" -ForegroundColor Green
        Write-Host ""
        Write-Host "Note: You'll be prompted for your SSH password" -ForegroundColor Yellow
    }
    
    "2" {
        Write-Host ""
        Write-Host "📦 Deploying via rsync..." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Run this command in your terminal:" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "rsync -avz --delete $localBuildPath/ $serverUser@${serverHost}:$serverPath/" -ForegroundColor Green
        Write-Host ""
        Write-Host "Note: You'll be prompted for your SSH password" -ForegroundColor Yellow
    }
    
    "3" {
        Write-Host ""
        Write-Host "📋 Manual FTP/SFTP Upload Instructions:" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "1. Open your FTP client (FileZilla, WinSCP, etc.)" -ForegroundColor White
        Write-Host "2. Connect to: $serverHost" -ForegroundColor White
        Write-Host "3. Navigate to: /home/solafriq/domains/solafriq.com/app/public/" -ForegroundColor White
        Write-Host "4. Upload the entire 'build' folder from:" -ForegroundColor White
        Write-Host "   $PWD\$localBuildPath" -ForegroundColor Yellow
        Write-Host "5. Ensure all files are uploaded (manifest.json + assets folder)" -ForegroundColor White
        Write-Host ""
        Write-Host "✅ After upload, verify these files exist on server:" -ForegroundColor Green
        Write-Host "   - /home/solafriq/domains/solafriq.com/app/public/build/manifest.json" -ForegroundColor White
        Write-Host "   - /home/solafriq/domains/solafriq.com/app/public/build/assets/*.js" -ForegroundColor White
        Write-Host "   - /home/solafriq/domains/solafriq.com/app/public/build/assets/*.css" -ForegroundColor White
    }
    
    "4" {
        Write-Host ""
        Write-Host "📦 Creating ZIP file..." -ForegroundColor Yellow
        
        $zipPath = "solafriq-build-assets.zip"
        
        if (Test-Path $zipPath) {
            Remove-Item $zipPath -Force
        }
        
        Compress-Archive -Path "$localBuildPath\*" -DestinationPath $zipPath -Force
        
        Write-Host "✅ ZIP file created: $zipPath" -ForegroundColor Green
        Write-Host ""
        Write-Host "📋 Upload Instructions:" -ForegroundColor Cyan
        Write-Host "1. Upload $zipPath to your server" -ForegroundColor White
        Write-Host "2. SSH into your server and run:" -ForegroundColor White
        Write-Host "   cd /home/solafriq/domains/solafriq.com/app/public" -ForegroundColor Yellow
        Write-Host "   unzip -o solafriq-build-assets.zip -d build/" -ForegroundColor Yellow
        Write-Host "   rm solafriq-build-assets.zip" -ForegroundColor Yellow
    }
    
    "5" {
        Write-Host "Exiting..." -ForegroundColor Yellow
        exit 0
    }
    
    default {
        Write-Host "❌ Invalid option selected" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "🔧 After deployment, run these commands on your server:" -ForegroundColor Cyan
Write-Host ""
Write-Host "cd /home/solafriq/domains/solafriq.com/app" -ForegroundColor Yellow
Write-Host "php artisan optimize:clear" -ForegroundColor Yellow
Write-Host "php artisan optimize" -ForegroundColor Yellow
Write-Host ""
Write-Host "✅ Deployment preparation complete!" -ForegroundColor Green
