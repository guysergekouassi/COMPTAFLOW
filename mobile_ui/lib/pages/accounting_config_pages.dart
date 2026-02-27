import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * AccountingConfigPage : Gestion de la structure comptable.
 * API Consommée : 
 * - GET /api/v1/accounting/plan-comptable
 * - GET /api/v1/accounting/plan-tiers
 * - GET /api/v1/accounting/journals
 * - GET /api/v1/accounting/treasury-posts
 */

class AccountingConfigPage extends StatelessWidget {
  const AccountingConfigPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        appBar: AppBar(
          title: const Text("Configuration", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.textDark),
            onPressed: () => Navigator.pop(context),
          ),
          bottom: const TabBar(
            isScrollable: true,
            labelColor: AppTheme.primaryBlue,
            unselectedLabelColor: AppTheme.textMuted,
            tabs: [
              Tab(text: "Plan Comptable"),
              Tab(text: "Plan Tiers"),
              Tab(text: "Journaux"),
              Tab(text: "Trésorerie"),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            _buildList("Compte"),
            _buildList("Tiers"),
            _buildList("Journal"),
            _buildList("Banque/Caisse"),
          ],
        ),
      ),
    );
  }

  Widget _buildList(String label) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: 10,
      itemBuilder: (context, index) {
        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFF1F5F9)),
          ),
          child: ListTile(
            title: Text("$label $index", style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            subtitle: Text("Description détaillée du $label", style: const TextStyle(fontSize: 11)),
            trailing: const Icon(Icons.chevron_right, size: 16),
          ),
        );
      },
    );
  }
}
