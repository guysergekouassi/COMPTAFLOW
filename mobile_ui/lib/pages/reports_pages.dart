import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * ReportsPage : Accès aux états financiers.
 * API Consommée : GET /api/v1/reports/bilan, /resultat, /tft, /balance
 */

class ReportsPage extends StatelessWidget {
  const ReportsPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("États Financiers", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.textDark),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            _buildReportCard(context, "Bilan Actif/Passif", "Vision globale du patrimoine", Icons.account_balance),
            _buildReportCard(context, "Compte de Résultat", "Analyse des charges et produits", Icons.pie_chart),
            _buildReportCard(context, "TFT (Flux Trésorerie)", "Suivi des encaissements/décaissements", Icons.synv_rounded),
            _buildReportCard(context, "Balance des Comptes", "Vérification des équilibres", Icons.balance),
          ],
        ),
      ),
    );
  }

  Widget _buildReportCard(BuildContext context, String title, String description, IconData icon) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [BoxShadow(color: AppTheme.cardShadow, blurRadius: 10, offset: Offset(0, 4))],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: AppTheme.iconBgBlue, borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: AppTheme.primaryBlue),
        ),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        subtitle: Text(description, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
        trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: AppTheme.textMuted),
        onTap: () {
          // Naviguer vers le détail du rapport
        },
      ),
    );
  }
}
