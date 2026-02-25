import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * AnalytiquePage : Gestion des ventilations et pilotage analytique.
 * API Consommée (Paramétrage) : 
 * - GET /api/v1/analytique/axes
 * - GET /api/v1/analytique/sections
 * - GET /api/v1/analytique/rules
 * API Consommée (Rapports) : 
 * - GET /api/v1/reports/analytique/balance
 * - GET /api/v1/reports/analytique/grand-livre
 * - GET /api/v1/reports/analytique/resultat
 */

class AnalytiquePage extends StatelessWidget {
  const AnalytiquePage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundSlate,
      appBar: AppBar(
        title: const Text("Pilotage Analytique", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
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
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildSectionHeader("STRUCTURE ANALYTIQUE"),
            _buildCollapseSection("AXES ANALYTIQUES", ["Axe Direction", "Axe Projet", "Axe Département"]),
            const SizedBox(height: 12),
            _buildCollapseSection("SECTIONS ANALYTIQUES", ["Siège social", "Projet IA", "Ventes Africa"]),
            const SizedBox(height: 12),
            _buildCollapseSection("RÈGLES DE VENTILATION", ["Règle par défaut (100% Siège)", "Règle Marketing (50/50)"]),
            
            const SizedBox(height: 32),
            _buildSectionHeader("RAPPORTS ANALYTIQUES"),
            _buildReportItem(context, Icons.account_balance_outlined, "Balance Analytique", "Par axe et section"),
            _buildReportItem(context, Icons.menu_book_outlined, "Grand Livre Analytique", "Détail des ventilations"),
            _buildReportItem(context, Icons.pie_chart_outline, "Résultat Analytique", "Produits - Charges ventilés"),
            
            const SizedBox(height: 32),
            _buildSectionHeader("DÉCISIONS & ACTIONS"),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {},
                icon: const Icon(Icons.analytics_outlined),
                label: const Text("GÉRER LES VENTILATIONS"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryBlue,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.all(16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, left: 4),
      child: Text(
        title,
        style: const TextStyle(
          color: AppTheme.textSecondary,
          fontSize: 11,
          fontWeight: FontWeight.bold,
          letterSpacing: 1.1,
        ),
      ),
    );
  }

  Widget _buildCollapseSection(String title, List<String> items) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: ExpansionTile(
        tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.textDark)),
        children: items.map((item) => ListTile(
          dense: true,
          title: Text(item, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
          trailing: const Icon(Icons.chevron_right, size: 14),
        )).toList(),
      ),
    );
  }

  Widget _buildReportItem(BuildContext context, IconData icon, String title, String subtitle) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(color: AppTheme.iconBgBlue, shape: BoxShape.circle),
          child: Icon(icon, color: AppTheme.primaryBlue, size: 20),
        ),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        subtitle: Text(subtitle, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
        trailing: const Icon(Icons.arrow_forward_ios, size: 14),
        onTap: () {},
      ),
    );
  }
}
