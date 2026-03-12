# -*- coding: utf-8 -*-
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyArrowPatch

fig, ax = plt.subplots(figsize=(22, 16))
ax.set_xlim(0, 22)
ax.set_ylim(0, 16)
ax.axis('off')
fig.patch.set_facecolor('#FAFAFA')

def draw_box(ax, x, y, w, h, title, fields, title_color, box_color, border_color, fontsize=7.5):
    rect = mpatches.FancyBboxPatch((x - w/2, y - h/2), w, h,
        boxstyle="round,pad=0.15", linewidth=2,
        edgecolor=border_color, facecolor=box_color, zorder=3)
    ax.add_patch(rect)
    # Title bar
    title_rect = mpatches.FancyBboxPatch((x - w/2, y + h/2 - 0.55), w, 0.55,
        boxstyle="round,pad=0.05", linewidth=0,
        edgecolor=border_color, facecolor=title_color, zorder=4)
    ax.add_patch(title_rect)
    ax.text(x, y + h/2 - 0.27, title, ha='center', va='center',
            fontsize=8.5, fontweight='bold', color='white', zorder=5)
    # Fields
    for i, f in enumerate(fields):
        ax.text(x, y + h/2 - 0.8 - i*0.38, f, ha='center', va='center',
                fontsize=fontsize, color='#333333', zorder=5)

def draw_arrow(ax, x1, y1, x2, y2):
    ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
        arrowprops=dict(arrowstyle="-|>", color='#1a1a2e',
                        lw=1.8, connectionstyle="arc3,rad=0.0"),
        zorder=2)

# ── TITLE ──
ax.text(11, 15.4, "SAT – Structure d'Accès Théorique – COMPTAFLOW",
        ha='center', va='center', fontsize=14, fontweight='bold', color='#1a1a2e')
ax.text(11, 14.95, "Méthode MERISE  |  Application COMPTAFLOW",
        ha='center', va='center', fontsize=9.5, color='#555555', style='italic')

# ── LEVEL 0 : COMPANIES ──
companies_fields = [
    "id  (PK)", "company_name", "juridique_form",
    "social_capital", "adresse / city", "email_adresse", "is_blocked"
]
draw_box(ax, 11, 12.8, 3.2, 3.2, "COMPANIES",
         companies_fields, '#1a1a2e', '#e8f0fe', '#1a1a2e')

# Label niveau
ax.text(0.4, 12.8, "NIVEAU\n  0", ha='center', va='center',
        fontsize=8.5, color='#1a1a2e', fontweight='bold',
        bbox=dict(boxstyle='round', facecolor='#d0d8f0', edgecolor='#1a1a2e', lw=1.2))

# ── LEVEL 1 : 6 entités ──
ax.text(0.4, 8.5, "NIVEAU\n   1", ha='center', va='center',
        fontsize=8.5, color='#b24a00', fontweight='bold',
        bbox=dict(boxstyle='round', facecolor='#ffe0cc', edgecolor='#b24a00', lw=1.2))

l1_entities = [
    ("id_user", ["id (PK)", "name / last_name", "email_adresse", "password (hash)", "role", "is_online"], 1.8),
    ("id_exercice", ["id (PK)", "date_debut", "date_fin", "is_active", "cloturer"], 5.0),
    ("id_plan_comptable", ["id (PK)", "numero_compte", "intitule", "classe", "type_compte", "adding_strategy"], 8.3),
    ("id_plan_tiers", ["id (PK)", "numero_tiers", "intitule", "adresse", "type_tiers"], 11.6),
    ("id_journal", ["id (PK)", "code_journal", "intitule", "type", "traite_anal", "compte_contrepartie"], 14.9),
    ("id_compte_treso", ["id (PK)", "name", "type", "solde_initial", "solde_actuel"], 18.6),
]

l1_h = 3.0
l1_y = 8.5
for title, fields, x in l1_entities:
    h = 0.55 + len(fields)*0.38 + 0.15
    draw_box(ax, x, l1_y, 2.8, h, title, fields, '#c05000', '#fff3e0', '#c05000')
    draw_arrow(ax, 11, 12.8 - 3.2/2, x, l1_y + h/2)

# ── LEVEL 2 : ECRITURE_COMPTABLES ──
ax.text(0.4, 3.7, "NIVEAU\n   2", ha='center', va='center',
        fontsize=8.5, color='#1a6e3c', fontweight='bold',
        bbox=dict(boxstyle='round', facecolor='#d4edda', edgecolor='#1a6e3c', lw=1.2))

ecriture_fields = [
    "id  (PK)", "date", "n_saisie_user / n_saisie",
    "description_operation", "reference_piece", "piece_justificatif",
    "debit", "credit", "statut", "type_flux", "plan_analytique"
]
e_h = 0.55 + len(ecriture_fields)*0.38 + 0.15
draw_box(ax, 11, 3.7, 3.8, e_h, "ECRITURE_COMPTABLES",
         ecriture_fields, '#155724', '#d4edda', '#155724')

# Arrows from level-1 to ECRITURE (only 4 that are related)
for title, fields, x in l1_entities:
    if title in ["id_plan_comptable", "id_plan_tiers", "id_journal", "id_compte_treso"]:
        h = 0.55 + len(fields)*0.38 + 0.15
        draw_arrow(ax, x, l1_y - h/2, 11, 3.7 + e_h/2)

# ── LEGEND ──
legend_x, legend_y = 0.5, 1.0
ax.annotate("", xy=(1.8, legend_y), xytext=(1.0, legend_y),
    arrowprops=dict(arrowstyle="-|>", color='#1a1a2e', lw=1.8))
ax.text(2.0, legend_y, "= Dépendance Fonctionnelle (DF)",
        va='center', fontsize=9, color='#1a1a2e')

ax.legend(handles=[
    mpatches.Patch(facecolor='#e8f0fe', edgecolor='#1a1a2e', lw=2, label='Identifiant racine (Niveau 0)'),
    mpatches.Patch(facecolor='#fff3e0', edgecolor='#c05000', lw=2, label='Entités satellites (Niveau 1)'),
    mpatches.Patch(facecolor='#d4edda', edgecolor='#155724', lw=2, label='Table centrale (Niveau 2)'),
], loc='lower right', fontsize=9, framealpha=0.95)

plt.tight_layout()
plt.savefig(r"c:\laragon\www\COMPTAFLOW\sat_comptaflow.png", dpi=180, bbox_inches='tight',
            facecolor='#FAFAFA', edgecolor='none')
print("SAT image saved: c:\\laragon\\www\\COMPTAFLOW\\sat_comptaflow.png")
