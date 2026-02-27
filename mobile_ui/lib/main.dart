import 'package:flutter/material.dart';
import 'theme/app_theme.dart';
import 'pages/dashboard_page.dart';
import 'pages/approvals_page.dart';
import 'pages/entries_list_page.dart';
import 'pages/new_entry_page.dart';
import 'pages/reports_pages.dart';
import 'pages/tasks_page.dart';
import 'pages/notifications_page.dart';
import 'pages/accounting_config_pages.dart';
import 'pages/immo_pages.dart';
import 'pages/analytique_pages.dart';

/**
 * Point d'entrée de l'application mobile COMPTAFLOW.
 * Définit toutes les routes correspondant aux modules API.
 */

void main() {
  runApp(const ComptaFlowApp());
}

class ComptaFlowApp extends StatelessWidget {
  const ComptaFlowApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'COMPTAFLOW Mobile',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: '/',
      routes: {
        '/': (context) => const DashboardPage(),
        '/approvals': (context) => const ApprovalsPage(),
        '/tasks': (context) => const TasksPage(),
        '/notifications': (context) => const NotificationsPage(),
        '/entries': (context) => const EntriesListPage(),
        '/entries/rejets': (context) => const EntriesListPage(initialFilter: "rejets"),
        '/entries/brouillons': (context) => const EntriesListPage(initialFilter: "brouillons"),
        '/new-entry': (context) => const NewEntryPage(),
        '/reports': (context) => const ReportsPage(),
        '/config': (context) => const AccountingConfigPage(),
        '/immo': (context) => const ImmoPage(),
        '/analytique': (context) => const AnalytiquePage(),
      },
    );
  }
}
