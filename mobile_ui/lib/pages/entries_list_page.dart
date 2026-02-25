import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * EntriesListPage : Liste des écritures comptables.
 * API Consommée : 
 * - /api/v1/entries (Toutes)
 * - /api/v1/entries/rejected (Rejetées)
 * - /api/v1/entries/drafts (Brouillons)
 */

class EntriesListPage extends StatefulWidget {
  final String initialFilter; // "toutes", "rejets", "brouillons"
  const EntriesListPage({Key? key, this.initialFilter = "toutes"}) : super(key: key);

  @override
  State<EntriesListPage> createState() => _EntriesListPageState();
}

class _EntriesListPageState extends State<EntriesListPage> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    if (widget.initialFilter == "rejets") _tabController.index = 1;
    if (widget.initialFilter == "brouillons") _tabController.index = 2;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Journal d'écritures", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.textDark),
          onPressed: () => Navigator.pop(context),
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppTheme.primaryBlue,
          unselectedLabelColor: AppTheme.textMuted,
          indicatorColor: AppTheme.primaryBlue,
          tabs: const [
            Tab(text: "Toutes"),
            Tab(text: "Rejetées"),
            Tab(text: "Brouillons"),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildList("toutes"),
          _buildList("rejets"),
          _buildList("brouillons"),
        ],
      ),
    );
  }

  Widget _buildList(String type) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: 5,
      itemBuilder: (context, index) {
        return _buildEntryItem(type);
      },
    );
  }

  Widget _buildEntryItem(String type) {
    Color statusColor = AppTheme.primaryBlue;
    String statusText = "VALIDÉ";
    
    if (type == "rejets") {
      statusColor = Colors.red;
      statusText = "REJETÉ";
    } else if (type == "brouillons") {
      statusColor = Colors.grey;
      statusText = "BROUILLON";
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text("PIÈCE #V$type-123", style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              Text(statusText, style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 10)),
            ],
          ),
          const SizedBox(height: 8),
          const Text("ACHAT FOURNITURES - BUREAU", style: TextStyle(color: AppTheme.textSecondary, fontSize: 12)),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text("15/02/2026", style: TextStyle(color: AppTheme.textMuted, fontSize: 11)),
              const Text("125 000 FCFA", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            ],
          ),
        ],
      ),
    );
  }
}
