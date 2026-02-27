import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * NewEntryPage : Formulaire de saisie d'écriture.
 * API Consommée : 
 * - POST /api/v1/entries (Enregistrement)
 * - POST /api/v1/scan (Scan IA Gemini)
 * - GET /api/v1/accounting/plan-comptable (Autocomplétion)
 */

class NewEntryPage extends StatelessWidget {
  const NewEntryPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Nouvelle Saisie", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close, color: AppTheme.textDark),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            _buildScanButton(),
            const SizedBox(height: 30),
            _buildInputField("Nouveau Numéro de Pièce"),
            const SizedBox(height: 16),
            _buildInputField("Date de l'opération", icon: Icons.calendar_today),
            const SizedBox(height: 16),
            _buildInputField("Libellé de l'écriture"),
            const SizedBox(height: 30),
            
            const Row(
              children: [
                Text("LIGNES COMPTABLES", style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.textMuted, fontSize: 11)),
              ],
            ),
            const Divider(),
            _buildEntryLine(),
            const SizedBox(height: 12),
            TextButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.add_circle_outline),
              label: const Text("Ajouter une ligne"),
            ),
            
            const SizedBox(height: 40),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryBlue,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text("ENREGISTRER L'ÉCRITURE", style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildScanButton() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [AppTheme.primaryBlue, Color(0xFF6366F1)]),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          const Icon(Icons.auto_awesome, color: Colors.white, size: 30),
          const SizedBox(height: 12),
          const Text("SCAN IA (GEMINI)", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
          const Text("Prenez votre facture en photo", style: TextStyle(color: Colors.white70, fontSize: 12)),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () {},
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white24,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text("DÉMARRER LE SCAN", style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildInputField(String label, {IconData? icon}) {
    return TextField(
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: icon != null ? Icon(icon, size: 20) : null,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      ),
    );
  }

  Widget _buildEntryLine() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.backgroundSlate,
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Column(
        children: [
          Row(
            children: [
              Expanded(child: Text("Compte: 601000", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
              Text("DÉBIT: 100 000 FCFA", style: TextStyle(color: Colors.blue, fontWeight: FontWeight.bold, fontSize: 12)),
            ],
          ),
          SizedBox(height: 4),
          Row(
            children: [
              Expanded(child: Text("Fournisseur Orange", style: TextStyle(color: AppTheme.textSecondary, fontSize: 11))),
            ],
          ),
        ],
      ),
    );
  }
}
