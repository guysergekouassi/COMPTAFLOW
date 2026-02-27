import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * NotificationsPage : Alertes et notifications système.
 * API Consommée : GET /api/v1/notifications, POST /api/v1/notifications/{id}/mark-as-read
 */

class NotificationsPage extends StatelessWidget {
  const NotificationsPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Notifications", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.textDark),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: 5,
        separatorBuilder: (context, index) => const Divider(height: 1),
        itemBuilder: (context, index) {
          return ListTile(
            contentPadding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
            leading: CircleAvatar(
              backgroundColor: index % 2 == 0 ? Colors.orange[50] : AppTheme.iconBgBlue,
              child: Icon(
                index % 2 == 0 ? Icons.warning_amber_rounded : Icons.info_outline,
                color: index % 2 == 0 ? Colors.orange : AppTheme.primaryBlue,
                size: 20,
              ),
            ),
            title: Text(
              index % 2 == 0 ? "Facture manquante" : "Nouvelle approbation",
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
            ),
            subtitle: Text(
              index % 2 == 0 
                ? "L'écriture #1234 n'a pas de pièce jointe."
                : "Une écriture de 50 000 FCFA attend votre validation.",
              style: const TextStyle(fontSize: 12),
            ),
            trailing: const Text("14:30", style: TextStyle(color: AppTheme.textMuted, fontSize: 10)),
            onTap: () {},
          );
        },
      ),
    );
  }
}
