# PadBro - Setup Guide

Complete guide to set up the PadBro backend (Laravel) and mobile app (Flutter) for development.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Backend Setup (Laravel)](#backend-setup-laravel)
3. [Flutter App Setup](#flutter-app-setup)
4. [Connecting Flutter to Backend](#connecting-flutter-to-backend)
5. [Testing on Physical Devices](#testing-on-physical-devices)
6. [Admin Panel Access](#admin-panel-access)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software

| Software | Version | Download |
|----------|---------|----------|
| Docker Desktop | Latest | [docker.com](https://www.docker.com/products/docker-desktop) |
| Flutter SDK | 3.x+ | [flutter.dev](https://docs.flutter.dev/get-started/install) |
| Xcode | Latest (macOS) | App Store |
| Android Studio | Latest | [developer.android.com](https://developer.android.com/studio) |
| VS Code / IDE | Latest | [code.visualstudio.com](https://code.visualstudio.com/) |

### Verify Installation

```bash
# Check Docker
docker --version
docker compose version

# Check Flutter
flutter --version
flutter doctor
```

---

## Backend Setup (Laravel)

### 1. Navigate to Backend Directory

```bash
cd backend
```

### 2. Copy Environment File

```bash
cp src/.env.example src/.env
```

### 3. Configure Environment Variables

Edit `src/.env` and set the following:

```env
APP_NAME=PadBro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=padbro
DB_USERNAME=laravel
DB_PASSWORD=password
```

### 4. Start Docker Containers

```bash
# Start all services (app, database, nginx)
docker compose -f docker-compose.dev.yml up -d
```

### 5. Install Dependencies & Setup Database

```bash
# Install Composer dependencies
docker compose -f docker-compose.dev.yml exec app composer install

# Generate application key
docker compose -f docker-compose.dev.yml exec app php artisan key:generate

# Run database migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate

# Seed initial data (optional)
docker compose -f docker-compose.dev.yml exec app php artisan db:seed

# Create storage symlink
docker compose -f docker-compose.dev.yml exec app php artisan storage:link
```

### 6. Verify Backend is Running

Open your browser and visit:
- **API**: http://localhost:8000/api
- **Admin Panel**: http://localhost:8000/manager

### 7. Create Admin User (First Time)

```bash
docker compose -f docker-compose.dev.yml exec app php artisan make:filament-user
```

Follow the prompts to create your admin account.

---

## Flutter App Setup

### 1. Navigate to Mobile Directory

```bash
cd mobile
```

### 2. Install Dependencies

```bash
flutter pub get
```

### 3. iOS Setup (macOS only)

```bash
cd ios
pod install
cd ..
```

### 4. Run the App

```bash
# Run on iOS Simulator
flutter run -d ios

# Run on Android Emulator
flutter run -d android

# Run on specific device
flutter devices  # List available devices
flutter run -d <device_id>
```

---

## Connecting Flutter to Backend

### For iOS Simulator / Android Emulator

The app is pre-configured to work with simulators/emulators:

**File: `lib/core/network/api_endpoints.dart`**

```dart
static String get baseUrl {
  if (Platform.isAndroid) {
    return 'http://10.0.2.2:8000/api'; // Android emulator
  } else {
    return 'http://localhost:8000/api'; // iOS simulator
  }
}
```

No changes needed for simulator/emulator testing.

---

## Testing on Physical Devices

Physical devices cannot access `localhost`. You need to use your computer's local IP address.

### 1. Find Your Computer's IP Address

**macOS:**
```bash
ipconfig getifaddr en0
```

**Windows:**
```bash
ipconfig
# Look for IPv4 Address under your WiFi adapter
```

**Linux:**
```bash
hostname -I
```

Example output: `192.168.1.100`

### 2. Update Flutter App Configuration

Edit `lib/core/network/api_endpoints.dart`:

```dart
// Replace localhost with your IP
static String get baseUrl {
  if (Platform.isAndroid) {
    return 'http://192.168.1.100:8000/api'; // Your IP
  } else {
    return 'http://192.168.1.100:8000/api'; // Your IP
  }
}
```

### 3. Update Backend Configuration

Edit `backend/src/.env`:

```env
APP_URL=http://192.168.1.100:8000
```

Clear Laravel config cache:

```bash
docker compose -f docker-compose.dev.yml exec app php artisan config:clear
```

### 4. Ensure Same Network

- Your computer and physical device must be on the **same WiFi network**
- Ensure your firewall allows connections on port 8000

### 5. Hot Restart Flutter App

```bash
# Press 'R' in terminal or run:
flutter run
```

---

## Admin Panel Access

### URL
```
http://localhost:8000/manager
```

### Features
- **Dashboard**: Overview of bookings and statistics
- **Users**: Manage customer accounts
- **Cities**: Add/edit city locations
- **Categories**: Manage court categories (Indoor/Outdoor)
- **Courts**: Create and manage padel courts
- **Bookings**: View and approve/reject bookings

### Creating Sample Data

```bash
# Create sample courts using tinker
docker compose -f docker-compose.dev.yml exec app php artisan tinker

# In tinker, run:
\App\Models\Court::factory()->count(10)->create();
```

---

## Troubleshooting

### Backend Issues

#### Docker containers not starting
```bash
# Stop all containers
docker compose -f docker-compose.dev.yml down

# Remove volumes and restart
docker compose -f docker-compose.dev.yml down -v
docker compose -f docker-compose.dev.yml up -d
```

#### Database connection error
```bash
# Check if database container is running
docker compose -f docker-compose.dev.yml ps

# Check database logs
docker compose -f docker-compose.dev.yml logs database
```

#### Permission issues with storage
```bash
docker compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache
docker compose -f docker-compose.dev.yml exec app chown -R www-data:www-data storage bootstrap/cache
```

### Flutter Issues

#### Dependency errors
```bash
flutter clean
flutter pub get
```

#### iOS build fails
```bash
cd ios
rm -rf Pods Podfile.lock
pod install --repo-update
cd ..
flutter run
```

#### Android build fails
```bash
cd android
./gradlew clean
cd ..
flutter run
```

### Connection Issues

#### "Connection refused" error
1. Verify backend is running: `docker compose -f docker-compose.dev.yml ps`
2. Check API endpoint URL in Flutter app
3. For physical devices, ensure correct IP address is set
4. Verify both devices are on same network

#### Images not loading on physical device
- Ensure `APP_URL` in `.env` uses your IP address, not `localhost`
- Clear Laravel config cache after changing `.env`

#### API returns 401 Unauthorized
- Check if authentication token is being sent
- Verify user is logged in
- Token may have expired - try logging in again

---

## Environment Configuration Reference

### Backend (.env)

| Variable | Development | Production |
|----------|-------------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost:8000` | `https://yourdomain.com` |
| `DB_HOST` | `database` | Your DB host |

### Flutter (api_endpoints.dart)

| Environment | URL |
|-------------|-----|
| iOS Simulator | `http://localhost:8000/api` |
| Android Emulator | `http://10.0.2.2:8000/api` |
| Physical Device | `http://YOUR_IP:8000/api` |
| Production | `https://yourdomain.com/api` |

---

## Quick Commands Reference

### Backend

```bash
# Start containers
docker compose -f docker-compose.dev.yml up -d

# Stop containers
docker compose -f docker-compose.dev.yml down

# View logs
docker compose -f docker-compose.dev.yml logs -f app

# Run artisan commands
docker compose -f docker-compose.dev.yml exec app php artisan <command>

# Clear all caches
docker compose -f docker-compose.dev.yml exec app php artisan optimize:clear
```

### Flutter

```bash
# Get dependencies
flutter pub get

# Run app
flutter run

# Build APK
flutter build apk --release

# Build iOS
flutter build ios --release

# Run tests
flutter test
```

---

## Support

For issues and questions:
- Create an issue in the repository
- Check existing documentation
- Review the `brief.md` for feature specifications

---

**Happy Coding!**
**Copyright 2026 BuildWithAngga Indonesia!**