import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * ImmoPage : Gestion des immobilisations.
 * API Consommée : 
 * - GET /api/v1/immobilisations (Liste)
 * - GET /api/v1/immobilisations/{id}/amortissements (Plan)
 */

class ImmoPage extends StatelessWidget {
  const ImmoPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Immobilisations", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppTheme.textDark),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 4,
        itemBuilder: (context, index) {
          return _buildImmoCard(context);
        },
      ),
    );
  }

  Widget _buildImmoCard(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [BoxShadow(color: AppTheme.cardShadow, blurRadius: 10, offset: Offset(0, 4))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text("VÉHICULE DE SERVICE", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              const Text("LINEAIRE", style: TextStyle(color: AppTheme.primaryBlue, fontWeight: FontWeight.bold, fontSize: 10)),
            ],
          ),
          const SizedBox(height: 8),
          const Text("Acquis le 01/01/2024 • Durée: 5 ans", style: TextStyle(color: AppTheme.textSecondary, fontSize: 12)),
          const Divider(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildValueCol("VAL. ORIGINE", "15 000 000 FCFA"),
              _buildValueCol("VNC", "11 000 000 FCFA"),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => _showAmortissement(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.backgroundSlate,
                foregroundColor: AppTheme.primaryBlue,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text("Voir Plan d'Amortissement", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildValueCol(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 10)),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
      ],
    );
  }

  void _showAmortissement(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20))),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text("PLAN D'AMORTISSEMENT", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
              const Divider(),
              Expanded(
                child: ListView.builder(
                  itemCount: 5,
                  itemBuilder: (context, index) {
                    return ListTile(
                      title: Text("ANNÉE ${2024 + index}"),
                      trailing: const Text("3 000 000 FCFA", style: TextStyle(fontWeight: FontWeight.bold)),
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
