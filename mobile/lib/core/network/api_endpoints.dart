import 'dart:io';

/// API endpoint constants
class ApiEndpoints {
  ApiEndpoints._();

  // Base URL - automatically selects based on platform
  // For physical device testing, change localhost to your computer's IP
  static String get baseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api'; // Android emulator
    } else {
      return 'http://localhost:8000/api'; // iOS simulator
    }
  }

  // For physical device testing, uncomment and use your IP:
  // static String get baseUrl => 'http://YOUR_IP:8000/api';
  // static const String baseUrl = 'https://your-production-url.com/api'; // Production

  // Auth endpoints
  static const String register = '/register';
  static const String login = '/login';
  static const String logout = '/logout';
  static const String user = '/user';
  static const String updateProfile = '/user/update';
  static const String changePassword = '/user/change-password';

  // City endpoints
  static const String cities = '/cities';
  static String cityDetails(String identifier) => '/cities/$identifier';

  // Court endpoints
  static const String courts = '/courts';
  static const String popularCourts = '/courts/popular';
  static const String featuredCourts = '/courts/featured';
  static String courtDetails(String id) => '/courts/$id';
  static String courtAvailableSlots(String id) => '/courts/$id/available-slots';

  // Booking endpoints
  static const String bookings = '/bookings';
  static const String nextBooking = '/bookings/next';
  static const String createBooking = '/bookings';
  static String confirmBooking(int id) => '/bookings/$id/confirm';
  static String cancelBooking(int id) => '/bookings/$id';
}
