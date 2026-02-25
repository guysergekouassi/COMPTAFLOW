import 'package:flutter/material.dart';

/**
 * Thème d'application COMPTAFLOW
 * Ce fichier définit les couleurs et styles typographiques pour correspondre 
 * exactement à la charte graphique de la version Web (Premium).
 */

class AppTheme {
  // --- Couleurs Primaires (Extraites de la capture d'écran) ---
  static const Color primaryBlue = Color(0xFF0047FF);
  static const Color backgroundSlate = Color(0xFFF8FAFC);
  static const Color cardShadow = Color(0x0A000000); // Ombre très légère (2-4%)
  
  // --- Couleurs de Texte ---
  static const Color textDark = Color(0xFF1E293B);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textMuted = Color(0xFF94A3B8);

  // --- Couleurs de Statistique (Pastels) ---
  static const Color iconBgBlue = Color(0xFFE0E7FF);
  static const Color iconBgPurple = Color(0xFFF3E8FF);
  static const Color iconBgGreen = Color(0xFFDCFCE7);
  static const Color iconBgBlack = Color(0xFFF1F5F9);

  static const Color iconColorBlue = Color(0xFF0047FF);
  static const Color iconColorPurple = Color(0xFF7C3AED);
  static const Color iconColorGreen = Color(0xFF10B981);
  static const Color iconColorBlack = Color(0xFF334155);

  static ThemeData get lightTheme {
    return ThemeData(
      primaryColor: primaryBlue,
      scaffoldBackgroundColor: backgroundSlate,
      fontFamily: 'Inter', // Assurez-vous d'ajouter GoogleFonts ou le fichier asset
      textTheme: const TextTheme(
        headlineMedium: TextStyle(
          color: textDark,
          fontWeight: FontWeight.bold,
          fontSize: 24,
        ),
        bodyMedium: TextStyle(
          color: textSecondary,
          fontSize: 14,
        ),
      ),
    );
  }

  // Style de dégradé pour le texte "Bord"
  static Shader textGradient = const LinearGradient(
    colors: [primaryBlue, Color(0xFF6366F1)],
  ).createShader(const Rect.fromLTWH(0.0, 0.0, 200.0, 70.0));
}
