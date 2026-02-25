import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * Widget ModuleTile : Bouton interactif pour les modules (Traitement, Paramétrage, Rapports).
 * Reproduit les lignes interactives du dashboard web.
 */

class ModuleTile extends StatelessWidget {
  final String title;
  final IconData icon;
  final VoidCallback onTap;
  final bool isPrimary; // Si vrai, affiche le style bleu "Nouvelle Saisie"

  const ModuleTile({
    Key? key,
    required this.title,
    required this.icon,
    required this.onTap,
    this.isPrimary = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isPrimary ? AppTheme.primaryBlue : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: isPrimary ? null : Border.all(color: const Color(0xFFF1F5F9)),
          boxShadow: [
            if (!isPrimary) 
              const BoxShadow(
                color: AppTheme.cardShadow,
                blurRadius: 5,
                offset: Offset(0, 2),
              ),
          ],
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 18,
              color: isPrimary ? Colors.white : AppTheme.primaryBlue,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  color: isPrimary ? Colors.white : AppTheme.textDark,
                  fontWeight: isPrimary ? FontWeight.bold : FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ),
            Icon(
              Icons.chevron_right,
              size: 16,
              color: isPrimary ? Colors.white70 : AppTheme.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}
