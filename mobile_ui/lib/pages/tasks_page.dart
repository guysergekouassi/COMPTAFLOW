import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/**
 * TasksPage : Interface pour le suivi des tâches quotidiennes.
 * API Consommée : GET /api/v1/tasks, POST /api/v1/tasks
 */

class TasksPage extends StatelessWidget {
  const TasksPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Mes Tâches", style: TextStyle(color: AppTheme.textDark, fontWeight: FontWeight.bold)),
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
            _buildSectionHeader("À FAIRE AUJOURD'HUI"),
            _buildTaskItem("Saisie des factures Orange", "Urgent", true),
            _buildTaskItem("Rapprochement bancaire BICICI", "Moyen", false),
            const SizedBox(height: 30),
            _buildSectionHeader("TÂCHES COMPLÉTÉES"),
            _buildTaskItem("Clôture Janvier 2026", "Terminé", false, isCompleted: true),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppTheme.primaryBlue,
        child: const Icon(Icons.add),
        onPressed: () {},
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Text(
        title,
        style: const TextStyle(color: AppTheme.textMuted, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 1.0),
      ),
    );
  }

  Widget _buildTaskItem(String title, String priority, bool isUrgent, {bool isCompleted = false}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Row(
        children: [
          Icon(
            isCompleted ? Icons.check_circle : Icons.circle_outlined,
            color: isCompleted ? Colors.green : (isUrgent ? Colors.red : AppTheme.textMuted),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    decoration: isCompleted ? TextDecoration.lineThrough : null,
                    color: isCompleted ? AppTheme.textMuted : AppTheme.textDark,
                  ),
                ),
                Text(priority, style: TextStyle(color: isUrgent ? Colors.red[300] : AppTheme.textMuted, fontSize: 11)),
              ],
            ),
          ),
          const Icon(Icons.more_vert, color: AppTheme.textMuted, size: 18),
        ],
      ),
    );
  }
}
