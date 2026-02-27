import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * Widget AppDrawer : Le menu latéral (Sidebar) de l'application.
 * Reproduit la hiérarchie et les sections du menu web.
 */

class AppDrawer extends StatelessWidget {
  const AppDrawer({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: Colors.white,
      child: Column(
        children: [
          _buildHeader(),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              children: [
                _buildSectionTitle("PILOTAGE (API: /v1/dashboard, /notifications, /tasks, /approvals)"),
                _buildMenuItem(context, Icons.person_outline, "Tableau de bord personnel", () {}, isActive: true),
                _buildMenuItem(context, Icons.notifications_none, "Notifications", () {
                  Navigator.pushNamed(context, '/notifications');
                }),
                _buildMenuItem(context, Icons.task_alt, "Mes Tâches", () {
                  Navigator.pushNamed(context, '/tasks');
                }),
                _buildMenuItem(context, Icons.fact_check_outlined, "Approbations", () {
                  Navigator.pushNamed(context, '/approvals');
                }),
                
                const SizedBox(height: 24),
                _buildSectionTitle("PARAMÉTRAGE (API: /v1/accounting/*)"),
                _buildMenuItem(context, Icons.account_tree_outlined, "Plan Comptable", () {
                  Navigator.pushNamed(context, '/config');
                }),
                _buildMenuItem(context, Icons.groups_outlined, "Plan Tiers", () {
                  Navigator.pushNamed(context, '/config');
                }),
                _buildMenuItem(context, Icons.book_outlined, "Journaux", () {
                  Navigator.pushNamed(context, '/config');
                }),
                _buildMenuItem(context, Icons.account_balance_wallet_outlined, "Postes de Trésorerie", () {
                  Navigator.pushNamed(context, '/config');
                }),
                
                const SizedBox(height: 24),
                _buildSectionTitle("TRAITEMENT & RAPPORTS (API: /v1/entries, /reports)"),
                _buildMenuItem(context, Icons.list_alt, "Écritures", () {
                  Navigator.pushNamed(context, '/entries');
                }),
                _buildMenuItem(context, Icons.bar_chart, "Reports & États Financiers", () {
                  Navigator.pushNamed(context, '/reports');
                }),
                _buildMenuItem(context, Icons.business_outlined, "Immobilisations", () {
                  Navigator.pushNamed(context, '/immo');
                }),
                _buildMenuItem(context, Icons.analytics_outlined, "Pilotage Analytique", () {
                  Navigator.pushNamed(context, '/analytique');
                }),
                _buildMenuItem(context, Icons.rule, "Règles Analytiques", () {
                  Navigator.pushNamed(context, '/analytique');
                }),
              ],
            ),
          ),
          _buildFooter(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 60, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.show_chart_rounded, color: AppTheme.primaryBlue, size: 30),
              const SizedBox(width: 10),
              const Text(
                "Flow Compta",
                style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text(
            "comptabilité orange",
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 12),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: AppTheme.iconBgBlue,
              borderRadius: BorderRadius.circular(4),
            ),
            child: const Text(
              "ADMIN",
              style: TextStyle(color: AppTheme.primaryBlue, fontWeight: FontWeight.bold, fontSize: 10),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      child: Text(
        title,
        style: const TextStyle(
          color: AppTheme.textMuted,
          fontSize: 10,
          fontWeight: FontWeight.bold,
          letterSpacing: 1.0,
        ),
      ),
    );
  }

  Widget _buildMenuItem(BuildContext context, IconData icon, String title, VoidCallback onTap, {bool isActive = false}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 4),
      decoration: BoxDecoration(
        color: isActive ? AppTheme.primaryBlue : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        dense: true,
        leading: Icon(
          icon,
          color: isActive ? Colors.white : AppTheme.textSecondary,
          size: 20,
        ),
        title: Text(
          title,
          style: TextStyle(
            color: isActive ? Colors.white : AppTheme.textDark,
            fontSize: 13,
            fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
          ),
        ),
        onTap: onTap,
      ),
    );
  }

  Widget _buildFooter() {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Row(
        children: [
          const CircleAvatar(
            radius: 16,
            backgroundColor: AppTheme.primaryBlue,
            child: Text("OA", style: TextStyle(color: Colors.white, fontSize: 12)),
          ),
          const SizedBox(width: 10),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text("admin", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                Text("admin@gmail.com", style: TextStyle(color: AppTheme.textSecondary, fontSize: 11)),
              ],
            ),
          ),
          const Icon(Icons.logout, color: Colors.redAccent, size: 18),
        ],
      ),
    );
  }
}
