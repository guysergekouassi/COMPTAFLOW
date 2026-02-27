import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../widgets/stat_card.dart';
import '../widgets/module_tile.dart';
import '../widgets/navigation_drawer.dart';

/**
 * DashboardPage : Accueil principale de l'application.
 * API Consommée : GET /api/v1/dashboard
 */

class DashboardPage extends StatelessWidget {
  const DashboardPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundSlate,
      drawer: const AppDrawer(),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppTheme.textDark),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_none, color: AppTheme.primaryBlue),
            onPressed: () {},
          ),
          const Padding(
            padding: EdgeInsets.only(right: 16),
            child: CircleAvatar(
              radius: 14,
              backgroundColor: AppTheme.primaryBlue,
              child: Text("OA", style: TextStyle(color: Colors.white, fontSize: 10)),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeaderTitle(),
            const SizedBox(height: 10),
            const Text(
              "Bienvenue, voici l'état actuel de votre exercice comptable.",
              style: TextStyle(color: AppTheme.textSecondary, fontSize: 12),
            ),
            const SizedBox(height: 24),
            
            // --- Grille de Statistiques (2x2) ---
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              childAspectRatio: 1.1,
              children: const [
                StatCard(
                  title: "ÉCRITURES DU MOIS",
                  value: "0",
                  subtitle: "Synthèse mensuelle",
                  icon: Icons.edit_note,
                  iconBgColor: AppTheme.iconBgBlue,
                  iconColor: AppTheme.iconColorBlue,
                  onTap: () => Navigator.pushNamed(context, '/entries'),
                ),
                StatCard(
                  title: "SOLDE TRÉSORERIE",
                  value: "252 584 FCFA",
                  subtitle: "Mise à jour en temps réel",
                  icon: Icons.account_balance_wallet,
                  iconBgColor: AppTheme.iconBgPurple,
                  iconColor: AppTheme.iconColorPurple,
                  onTap: () => Navigator.pushNamed(context, '/config'),
                ),
                StatCard(
                  title: "TIERS ACTIFS",
                  value: "3",
                  subtitle: "3 CLIENTS | 0 FOURN.",
                  icon: Icons.people_outline,
                  iconBgColor: AppTheme.iconBgGreen,
                  iconColor: AppTheme.iconColorGreen,
                  onTap: () => Navigator.pushNamed(context, '/config'),
                ),
                StatCard(
                  title: "EXERCICE EN COURS",
                  value: "2025",
                  subtitle: "ACTIVÉ",
                  icon: Icons.calendar_today,
                  iconBgColor: AppTheme.iconBgBlack,
                  iconColor: AppTheme.iconColorBlack,
                  onTap: () {},
                ),
              ],
            ),
            
            const SizedBox(height: 40),
            
            // --- Section Modules de Travail ---
            const Row(
              children: [
                Icon(Icons.grid_view_rounded, color: AppTheme.primaryBlue),
                SizedBox(width: 10),
                Text(
                  "Vos Modules de Travail",
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                ),
              ],
            ),
            const SizedBox(height: 24),
            
            _buildModuleSection("TRAITEMENT", [
              ModuleTile(title: "Nouvelle Saisie", icon: Icons.add_box, isPrimary: true, onTap: () {
                Navigator.pushNamed(context, '/new-entry');
              }),
              ModuleTile(title: "Écritures", icon: Icons.list_alt, onTap: () {
                Navigator.pushNamed(context, '/entries');
              }),
              ModuleTile(title: "Écritures rejetées", icon: Icons.error_outline, onTap: () {
                Navigator.pushNamed(context, '/entries/rejets');
              }),
              ModuleTile(title: "Brouillons", icon: Icons.drafts_outlined, onTap: () {
                Navigator.pushNamed(context, '/entries/brouillons');
              }),
            ]),
            
            _buildModuleSection("PARAMÉTRAGE", [
              ModuleTile(title: "Plan Comptable", icon: Icons.account_tree_outlined, onTap: () {
                Navigator.pushNamed(context, '/config');
              }),
              ModuleTile(title: "Plan Tiers", icon: Icons.groups_outlined, onTap: () {
                Navigator.pushNamed(context, '/config');
              }),
              ModuleTile(title: "Journaux", icon: Icons.book_outlined, onTap: () {
                Navigator.pushNamed(context, '/config');
              }),
            ]),
            
            _buildModuleSection("RAPPORTS", [
              ModuleTile(title: "Grand Livre", icon: Icons.menu_book, onTap: () {
                Navigator.pushNamed(context, '/reports');
              }),
              ModuleTile(title: "Balance", icon: Icons.balance, onTap: () {
                Navigator.pushNamed(context, '/reports');
              }),
            ]),
            
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard(String title, List<Widget> children) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildHeaderTitle() {
    return Row(
      children: [
        const Text(
          "Tableau de ",
          style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppTheme.textDark),
        ),
        ShaderMask(
          shaderCallback: (bounds) => AppTheme.textGradient,
          child: const Text(
            "Bord",
            style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white),
          ),
        ),
      ],
    );
  }

  Widget _buildModuleSection(String title, List<Widget> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 12,
            fontWeight: FontWeight.bold,
            letterSpacing: 1.2,
          ),
        ),
        const Divider(height: 24, thickness: 1),
        ...items,
        const SizedBox(height: 20),
      ],
    );
  }
}
