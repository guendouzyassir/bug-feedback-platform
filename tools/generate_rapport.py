#!/usr/bin/env python3
"""Generate the internship rapport de stage PDF."""

from fpdf import FPDF
import os

OUTPUT = os.path.join(os.path.dirname(__file__), "..", "Rapport_de_Stage.pdf")


class RapportPDF(FPDF):
    def header(self):
        if self.page_no() > 1:
            self.set_font("Helvetica", "I", 8)
            self.set_text_color(120, 120, 120)
            self.cell(0, 8, "Rapport de Stage - Plateforme de Gestion de Bugs", align="C")
            self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font("Helvetica", "I", 8)
        self.set_text_color(120, 120, 120)
        self.cell(0, 10, f"Page {self.page_no()}/{{nb}}", align="C")

    def chapter_title(self, num, title):
        self.set_font("Helvetica", "B", 16)
        self.set_text_color(31, 95, 224)
        self.cell(0, 12, f"Chapitre {num} : {title}", new_x="LMARGIN", new_y="NEXT")
        self.set_draw_color(31, 95, 224)
        self.line(self.l_margin, self.get_y(), self.w - self.r_margin, self.get_y())
        self.ln(8)

    def section_title(self, title):
        self.set_font("Helvetica", "B", 13)
        self.set_text_color(23, 70, 162)
        self.cell(0, 10, title, new_x="LMARGIN", new_y="NEXT")
        self.ln(3)

    def sub_section(self, title):
        self.set_font("Helvetica", "B", 11)
        self.set_text_color(40, 40, 40)
        self.cell(0, 8, title, new_x="LMARGIN", new_y="NEXT")
        self.ln(2)

    def body_text(self, text):
        self.set_font("Helvetica", "", 10)
        self.set_text_color(30, 30, 30)
        self.multi_cell(0, 6, text)
        self.ln(3)

    def bullet(self, text):
        self.set_font("Helvetica", "", 10)
        self.set_text_color(30, 30, 30)
        x = self.get_x()
        self.cell(8, 6, "-")
        self.multi_cell(0, 6, text)
        self.ln(1)

    def table_header(self, cols, widths):
        self.set_font("Helvetica", "B", 9)
        self.set_fill_color(31, 95, 224)
        self.set_text_color(255, 255, 255)
        for i, col in enumerate(cols):
            self.cell(widths[i], 8, col, border=1, fill=True, align="C")
        self.ln()

    def table_row(self, cols, widths, fill=False):
        self.set_font("Helvetica", "", 9)
        self.set_text_color(30, 30, 30)
        if fill:
            self.set_fill_color(240, 245, 255)
        else:
            self.set_fill_color(255, 255, 255)
        for i, col in enumerate(cols):
            self.cell(widths[i], 7, str(col), border=1, fill=True, align="C")
        self.ln()


def build():
    pdf = RapportPDF()
    pdf.alias_nb_pages()
    pdf.set_auto_page_break(auto=True, margin=20)

    # ── COVER PAGE ──
    pdf.add_page()
    pdf.ln(50)
    pdf.set_font("Helvetica", "B", 28)
    pdf.set_text_color(31, 95, 224)
    pdf.cell(0, 15, "Rapport de Stage", align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(8)
    pdf.set_font("Helvetica", "", 18)
    pdf.set_text_color(60, 60, 60)
    pdf.cell(
        0,
        12,
        "Plateforme de Gestion de Bugs et de Feedback",
        align="C",
        new_x="LMARGIN",
        new_y="NEXT",
    )
    pdf.ln(20)
    pdf.set_draw_color(31, 95, 224)
    pdf.line(60, pdf.get_y(), pdf.w - 60, pdf.get_y())
    pdf.ln(20)
    pdf.set_font("Helvetica", "", 12)
    pdf.set_text_color(80, 80, 80)
    pdf.cell(0, 8, "Stagiaire : Yassir", align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 8, "Annee universitaire : 2025 / 2026", align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(30)
    pdf.set_font("Helvetica", "I", 10)
    pdf.set_text_color(140, 140, 140)
    pdf.cell(
        0,
        8,
        "Technologies : Symfony 7.4 | PHP 8.2 | Doctrine ORM | SQLite | Twig | Hotwired Turbo",
        align="C",
        new_x="LMARGIN",
        new_y="NEXT",
    )

    # ── TABLE OF CONTENTS ──
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 20)
    pdf.set_text_color(31, 95, 224)
    pdf.cell(0, 15, "Table des Matieres", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)
    toc = [
        ("Introduction generale", ""),
        ("Chapitre 1 : Présentation du stage et de l'entreprise", ""),
        ("   1.1  Contexte du stage", ""),
        ("   1.2  Presentation de l'entreprise d'accueil", ""),
        ("   1.3  Mission confiee", ""),
        ("Chapitre 2 : Presentation du projet", ""),
        ("   2.1  Objectifs du projet", ""),
        ("   2.2  Cahier des charges fonctionnel", ""),
        ("   2.3  Roles et utilisateurs", ""),
        ("Chapitre 3 : Architecture technique", ""),
        ("   3.1  Stack technologique", ""),
        ("   3.2  Structure du projet", ""),
        ("   3.3  Patron de conception MVC", ""),
        ("Chapitre 4 : Modele de donnees", ""),
        ("   4.1  Diagramme Entite-Relation", ""),
        ("   4.2  Description des entites", ""),
        ("   4.3  Enums et workflow des statuts", ""),
        ("Chapitre 5 : Fonctionnalites detaillees", ""),
        ("   5.1  Authentification et gestion des sessions", ""),
        ("   5.2  Tableaux de bord par role", ""),
        ("   5.3  Gestion des rapports de bugs", ""),
        ("   5.4  Systeme de filtrage", ""),
        ("   5.5  Gestion des projets et utilisateurs", ""),
        ("   5.6  Systeme de commentaires", ""),
        ("   5.7  Upload de captures d'ecran", ""),
        ("Chapitre 6 : Securite", ""),
        ("   6.1  Authentification et hachage des mots de passe", ""),
        ("   6.2  Controle d'acces base sur les roles", ""),
        ("   6.3  Protection CSRF", ""),
        ("   6.4  Validation des entrees", ""),
        ("Chapitre 7 : Tests", ""),
        ("   7.1  Tests unitaires", ""),
        ("   7.2  Tests fonctionnels", ""),
        ("Conclusion generale", ""),
        ("Annexes", ""),
    ]
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(40, 40, 40)
    for title, _page in toc:
        if title.startswith("Chapitre") or title.startswith("Introduction") or title.startswith("Conclusion") or title.startswith("Annexes"):
            pdf.set_font("Helvetica", "B", 11)
        else:
            pdf.set_font("Helvetica", "", 10)
        pdf.cell(0, 7, title, new_x="LMARGIN", new_y="NEXT")

    # ── INTRODUCTION ──
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 18)
    pdf.set_text_color(31, 95, 224)
    pdf.cell(0, 12, "Introduction generale", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)
    pdf.body_text(
        "Le presente rapport detaille le stage realise dans le cadre de ma formation en "
        "developpement web. Ce stage avait pour objectif de mettre en pratique les "
        "connaissances theoriques acquises au cours de ma formation, tout en decouvrant "
        "les realites du developpement logiciel en entreprise."
    )
    pdf.body_text(
        "La mission qui m'a ete confiee consistait a concevoir et developper une "
        "plateforme web de gestion de bugs et de feedback. Cette application interne "
        "permet aux equipes de development, aux clients et aux administrateurs de "
        "collaborer efficacement dans le suivi et la resolution des anomalies logicielles."
    )
    pdf.body_text(
        "Ce rapport est organise en sept chapitres. Le premier presente le contexte du "
        "stage et l'entreprise d'accueil. Le deuxieme decrit le projet et ses objectifs. "
        "Le troisieme detalles l'architecture technique retenue. Le quatrieme expose le "
        "modele de donnees. Le cinquieme decrit les fonctionnalites implementees. Le "
        "sixieme aborde les aspects de securite. Enfin, le septieme presente la "
        "strategie de tests."
    )

    # ── CHAPTER 1 ──
    pdf.add_page()
    pdf.chapter_title(1, "Presentation du stage et de l'entreprise")

    pdf.section_title("1.1 Contexte du stage")
    pdf.body_text(
        "Ce stage s'inscrit dans le cadre de la fin de formation en developpement web. "
        "D'une duree de plusieurs semaines, il m'a permis de travailler sur un projet "
        "concret de A a Z, depuis l'analyse des besoins jusqu'au deploiement de "
        "l'application."
    )
    pdf.body_text(
        "L'objectif principal etait de developper une application web complete en "
        "utilisant des technologies modernes, tout en appliquant les bonnes pratiques "
        "de developpement logiciel : architecture MVC, tests, securite, et gestion de "
        "version avec Git."
    )

    pdf.section_title("1.2 Presentation de l'entreprise d'accueil")
    pdf.body_text(
        "L'entreprise d'accueil est une organisation specialisee dans le developpement "
        "logiciel. Elle intervient dans differents domaines incluant le web, le mobile "
        "et les API. L'equipe utilise des methodologies agiles et des outils modernes "
        "pour garantir la qualite de ses livrables."
    )
    pdf.body_text(
        "L'entreprise possede plusieurs projets actifs : un site web marketing, une "
        "application mobile de commande, un tableau de bord CRM interne, et une API "
        "REST pour les partenaires exterieurs."
    )

    pdf.section_title("1.3 Mission confiee")
    pdf.body_text(
        "Ma mission consistait a concevoir et developper une plateforme centralisee de "
        "gestion de bugs et de feedback. Cette application devait permettre :"
    )
    pdf.bullet("La creation et le suivi des rapports de bugs par les clients/testeurs")
    pdf.bullet("L'assignation et le traitement des bugs par les developpeurs")
    pdf.bullet("La supervision et l'administration globale par les administrateurs")
    pdf.bullet("La gestion des projets et des utilisateurs")
    pdf.bullet("Le systeme de commentaires pour faciliter la communication")

    # ── CHAPTER 2 ──
    pdf.add_page()
    pdf.chapter_title(2, "Presentation du projet")

    pdf.section_title("2.1 Objectifs du projet")
    pdf.body_text(
        "La plateforme de gestion de bugs vise a resoudre les problemes lies a la "
        "communication des anomalies dans un contexte de developpement logiciel "
        "multi-acteurs. Les objectifs principaux sont :"
    )
    pdf.bullet("Centraliser tous les rapports de bugs dans un seul outil")
    pdf.bullet("Automatiser le workflow de resolution des bugs")
    pdf.bullet("Fournir des tableaux de bord pour chaque type d'utilisateur")
    pdf.bullet("Assurer la traibilite complete d'un bug (creation a resolution)")
    pdf.bullet("Faciliter la collaboration via les commentaires et les captures d'ecran")

    pdf.section_title("2.2 Cahier des charges fonctionnel")
    pdf.body_text("Les fonctionnalites principales de l'application sont :")
    pdf.bullet("Authentification securisee par email et mot de passe")
    pdf.bullet("Tableaux de bord personnalises par role (Admin, Developpeur, Client)")
    pdf.bullet("CRUD complet pour les rapports de bugs avec champs detailles")
    pdf.bullet("Systeme de filtrage avance (mot-cle, projet, statut, priorite, developpeur, dates)")
    pdf.bullet("Gestion des statuts avec workflow defini (Open -> InProgress -> Fixed/Rejected)")
    pdf.bullet("Assignation des bugs aux developpeurs par l'administrateur")
    pdf.bullet("Systeme de commentaires par thread de bug")
    pdf.bullet("Upload et affichage de captures d'ecran")
    pdf.bullet("Gestion complete des projets et des utilisateurs (admin)")
    pdf.bullet("Statistiques et metriques sur les tableaux de bord")

    pdf.section_title("2.3 Roles et utilisateurs")
    pdf.body_text("L'application gere trois roles distincts avec des niveaux d'acces differents :")

    pdf.sub_section("ROLE_ADMIN (Administrateur)")
    pdf.bullet("Acces complet a toutes les fonctionnalites")
    pdf.bullet("Gestion des projets (creation, modification, suppression)")
    pdf.bullet("Gestion des utilisateurs (creation, modification, desactivation)")
    pdf.bullet("Assignation des bugs aux developpeurs")
    pdf.bullet("Modification de la priorite et du statut de n'importe quel bug")
    pdf.bullet("Suppression des bugs")
    pdf.bullet("Tableau de bord avec statistiques globales")

    pdf.sub_section("ROLE_DEVELOPEUR (Developpeur)")
    pdf.bullet("Visualisation de tous les rapports de bugs")
    pdf.bullet("Mise a jour du statut des bugs qui lui sont assignes")
    pdf.bullet("Ajout de commentaires sur les bugs")
    pdf.bullet("Tableau de bord avec compteur des bugs assignes")

    pdf.sub_section("ROLE_CLIENT (Client / Testeur)")
    pdf.bullet("Creation de nouveaux rapports de bugs")
    pdf.bullet("Visualisation de ses propres rapports de bugs")
    pdf.bullet("Ajout de commentaires sur ses bugs")
    pdf.bullet("Tableau de bord avec compteur des bugs signales")

    # ── CHAPTER 3 ──
    pdf.add_page()
    pdf.chapter_title(3, "Architecture technique")

    pdf.section_title("3.1 Stack technologique")
    pdf.body_text("La plateforme a ete developpee avec les technologies suivantes :")

    cols = ["Composant", "Technologie", "Version"]
    widths = [50, 60, 40]
    pdf.table_header(cols, widths)
    data = [
        ["Langage", "PHP", ">= 8.2"],
        ["Framework", "Symfony", "7.4"],
        ["ORM", "Doctrine ORM", "3.6"],
        ["Moteur de templates", "Twig", "3.x"],
        ["Base de donnees", "SQLite", "Dev / PostgreSQL (prod)"],
        ["Frontend", "AssetMapper + Turbo", "8.0"],
        ["Authentification", "Symfony Security", "7.4"],
        ["Validation", "Symfony Validator", "7.4"],
        ["Tests", "PHPUnit", "13.x"],
    ]
    for i, row in enumerate(data):
        pdf.table_row(row, widths, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.section_title("3.2 Structure du projet")
    pdf.body_text("La structure des repertoires suit les conventions Symfony :")

    dirs = [
        ("src/Entity/", "Entites Doctrine ORM (User, BugReport, BugComment, Project)"),
        ("src/Enum/", "Enumerations PHP 8.1+ (BugStatus, BugPriority)"),
        ("src/Controller/", "Controleurs Symfony (routes et logique metier)"),
        ("src/Form/", "Types de formulaire Symfony"),
        ("src/Repository/", "Repositories Doctrine (requetes DQL)"),
        ("src/Service/", "Services metier (FileUploader)"),
        ("src/Security/", "Composants de securite (UserChecker)"),
        ("templates/", "Templates Twig (vues HTML)"),
        ("config/packages/", "Configuration des bundles Symfony"),
        ("migrations/", "Migrations Doctrine ORM"),
        ("tests/", "Tests PHPUnit"),
        ("assets/", "Assets JavaScript (Stimulus, Turbo)"),
    ]
    cols2 = ["Repertoire", "Description"]
    widths2 = [45, 125]
    pdf.table_header(cols2, widths2)
    for i, (d, desc) in enumerate(dirs):
        pdf.set_font("Helvetica", "", 8)
        pdf.table_row([d, desc], widths2, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.section_title("3.3 Patron de conception MVC")
    pdf.body_text(
        "L'application suit le patron Model-View-Controller (MVC) propre a Symfony. "
        "Le modele est represente par les entites Doctrine ORM, les vues sont des "
        "templates Twig, et les controleurs gerent la logique applicative et les routes."
    )
    pdf.body_text(
        "Le flux de traitement suit le cycle classique : une requete HTTP arrive, le "
        "routeur Symfony l'associe a une methode de controleur, le controleur interagit "
        "avec le modele (entites, repositories, services), et retourne une reponse "
        "constituee d'un template Twig rendu."
    )

    # ── CHAPTER 4 ──
    pdf.add_page()
    pdf.chapter_title(4, "Modele de donnees")

    pdf.section_title("4.1 Diagramme Entite-Relation")
    pdf.body_text(
        "Le modele de donnees est compose de quatre entites principales lies par des "
        "relations definies via les attributs Doctrine ORM :"
    )
    pdf.body_text(
        "  User  1 --- *  BugReport  (reporter)\n"
        "  User  1 --- *  BugReport  (assignedDeveloper)\n"
        "  User  1 --- *  BugComment (author)\n"
        "  Project 1 --- * BugReport\n"
        "  BugReport 1 --- * BugComment"
    )

    pdf.section_title("4.2 Description des entites")

    pdf.sub_section("Entite User (app_user)")
    cols3 = ["Champ", "Type", "Contraintes"]
    widths3 = [40, 35, 95]
    pdf.table_header(cols3, widths3)
    user_fields = [
        ["id", "int", "Cle primaire, auto-generee"],
        ["email", "string(180)", "Unique, NotBlank, Email"],
        ["roles", "json", "Tableau de roles"],
        ["password", "string", "Hache (auto hasher)"],
        ["fullName", "string(120)", "NotBlank, max 120"],
        ["isActive", "boolean", "Defaut: true"],
        ["createdAt", "DateTimeImmutable", "Defaut: date courante"],
    ]
    for i, row in enumerate(user_fields):
        pdf.table_row(row, widths3, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.sub_section("Entite BugReport")
    pdf.table_header(cols3, widths3)
    bug_fields = [
        ["id", "int", "Cle primaire"],
        ["title", "string(180)", "NotBlank, max 180"],
        ["description", "text", "NotBlank"],
        ["stepsToReproduce", "text?", "Optionnel"],
        ["expectedResult", "text?", "Optionnel"],
        ["actualResult", "text?", "Optionnel"],
        ["priority", "enum(BugPriority)", "Defaut: Medium"],
        ["status", "enum(BugStatus)", "Defaut: Open"],
        ["screenshotFilename", "string(255)?", "Optionnel"],
        ["createdAt", "DateTimeImmutable", "Date de creation"],
        ["updatedAt", "DateTimeImmutable", "Derniere mise a jour"],
        ["openedAt", "DateTimeImmutable?", "Date d'ouverture"],
        ["treatedAt", "DateTimeImmutable?", "Date de traitement"],
        ["closedAt", "DateTimeImmutable?", "Date de fermeture"],
        ["project_id", "FK -> Project", "NotNull"],
        ["reporter_id", "FK -> User", "NotNull"],
        ["assignedDeveloper_id", "FK -> User", "Optionnel"],
    ]
    for i, row in enumerate(bug_fields):
        pdf.table_row(row, widths3, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.sub_section("Entite BugComment")
    pdf.table_header(cols3, widths3)
    comment_fields = [
        ["id", "int", "Cle primaire"],
        ["content", "text", "NotBlank"],
        ["createdAt", "DateTimeImmutable", "Date de creation"],
        ["bugReport_id", "FK -> BugReport", "NotNull, onDelete CASCADE"],
        ["author_id", "FK -> User", "NotNull"],
    ]
    for i, row in enumerate(comment_fields):
        pdf.table_row(row, widths3, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.sub_section("Entite Project")
    pdf.table_header(cols3, widths3)
    project_fields = [
        ["id", "int", "Cle primaire"],
        ["name", "string(120)", "NotBlank, max 120"],
        ["description", "text?", "Optionnel"],
        ["platform", "string(60)", "NotBlank (Web, Mobile, API)"],
        ["isActive", "boolean", "Defaut: true"],
        ["createdAt", "DateTimeImmutable", "Date de creation"],
        ["updatedAt", "DateTimeImmutable", "Derniere mise a jour"],
    ]
    for i, row in enumerate(project_fields):
        pdf.table_row(row, widths3, fill=(i % 2 == 0))

    pdf.ln(5)
    pdf.section_title("4.3 Enums et workflow des statuts")
    pdf.body_text("Les enumerations PHP 8.1+ assurent la type-safety des valeurs :")

    pdf.sub_section("BugStatus (Statut du bug)")
    cols4 = ["Valeur", "Label", "Description"]
    widths4 = [35, 35, 100]
    pdf.table_header(cols4, widths4)
    statuses = [
        ["open", "Open", "Bug nouveau, en attente de traitement"],
        ["in_progress", "In Progress", "Bug en cours de traitement"],
        ["fixed", "Fixed", "Bug corrige (supprime du systeme)"],
        ["rejected", "Rejected", "Bug rejete (non reproductible, etc.)"],
        ["closed", "Closed", "Bug ferme definitivement"],
    ]
    for i, row in enumerate(statuses):
        pdf.table_row(row, widths4, fill=(i % 2 == 0))

    pdf.ln(3)
    pdf.body_text(
        "Workflow des statuts : Open -> InProgress -> Fixed | Rejected -> Closed. "
        "Lorsqu'un bug passe a 'Fixed', il est automatiquement supprime du systeme "
        "avec sa capture d'ecran associee."
    )

    pdf.sub_section("BugPriority (Priorite du bug)")
    cols5 = ["Valeur", "Label"]
    widths5 = [40, 40]
    pdf.table_header(cols5, widths5)
    priorities = [
        ["low", "Low (Basse)"],
        ["medium", "Medium (Moyenne)"],
        ["high", "High (Haute)"],
        ["critical", "Critical (Critique)"],
    ]
    for i, row in enumerate(priorities):
        pdf.table_row(row, widths5, fill=(i % 2 == 0))

    # ── CHAPTER 5 ──
    pdf.add_page()
    pdf.chapter_title(5, "Fonctionnalites detaillees")

    pdf.section_title("5.1 Authentification et gestion des sessions")
    pdf.body_text(
        "L'authentification est implementee via le composant Security de Symfony. "
        "Les utilisateurs se connectent avec leur adresse email et mot de passe. "
        "Le systeme utilise un formulaire de login avec protection CSRF."
    )
    pdf.body_text(
        "Les mots de passe sont haches automatiquement avec l'algorithme auto "
        "de Symfony (Argon2i/BCrypt selon le systeme). Un UserChecker verifie "
        "que le compte est actif avant chaque requete authentifiee."
    )
    pdf.body_text(
        "Apres la connexion, l'utilisateur est redirige vers son tableau de bord "
        "personnalise selon son role : /admin/dashboard, /developer/dashboard, "
        "ou /client/dashboard."
    )

    pdf.section_title("5.2 Tableaux de bord par role")
    pdf.sub_section("Tableau de bord Administrateur")
    pdf.body_text(
        "Le tableau de bord administrateur affiche des metriques globales : nombre "
        "total de bugs, bugs ouverts, en cours, fixes, critiques et non assignes. "
        "Il presente egalement des tableaux de repartition par statut et par projet, "
        "ainsi que des liens rapides vers la gestion des bugs, des projets et des "
        "utilisateurs."
    )
    pdf.sub_section("Tableau de bord Developpeur")
    pdf.body_text(
        "Le developpeur voit le nombre de bugs qui lui sont assignes, avec des liens "
        "rapides vers la liste des bugs et la creation d'un nouveau bug."
    )
    pdf.sub_section("Tableau de bord Client")
    pdf.body_text(
        "Le client voit le nombre de bugs qu'il a signales, avec des liens rapides "
        "vers la liste de ses bugs et la creation d'un nouveau bug."
    )

    pdf.section_title("5.3 Gestion des rapports de bugs")
    pdf.body_text(
        "La creation d'un rapport de bug inclut les champs suivants : titre, "
        "description detaillee, etapes pour reproduire le bug, resultat attendu, "
        " resultat obtenu, priorite, projet associe, et capture d'ecran optionnelle."
    )
    pdf.body_text(
        "Chaque bug est automatiquement lie a l'utilisateur rapporteur et recoit "
        "le statut 'Open' a sa creation. Les timestamps sont tracks automatiquement : "
        "date de creation, date d'ouverture, date de traitement, et date de fermeture."
    )
    pdf.body_text(
        "La vue detaillee d'un bug affiche toutes les informations, un chronometre "
        "de suivi, le formulaire de mise a jour du statut (pour les developpeurs), "
        "et la liste des commentaires."
    )

    pdf.section_title("5.4 Systeme de filtrage")
    pdf.body_text("La page d'index des bugs offre 7 filtres combinaisons :")
    pdf.bullet("Recherche par mot-cle (titre et description)")
    pdf.bullet("Filtrage par projet")
    pdf.bullet("Filtrage par statut (Open, InProgress, Fixed, Rejected, Closed)")
    pdf.bullet("Filtrage par priorite (Low, Medium, High, Critical)")
    pdf.bullet("Filtrage par developpeur assigne (admin/developpeur uniquement)")
    pdf.bullet("Filtrage par date de debut")
    pdf.bullet("Filtrage par date de fin")
    pdf.body_text(
        "Les clients ne voient que leurs propres bugs, tandis que les admins et "
        "developpeurs voient tous les bugs du systeme."
    )

    pdf.section_title("5.5 Gestion des projets et utilisateurs")
    pdf.body_text(
        "L'administrateur peut creer, modifier et supprimer des projets. Chaque projet "
        "a un nom, une description, une plateforme (Web, Mobile, API) et un statut "
        "actif/inactif."
    )
    pdf.body_text(
        "La gestion des utilisateurs permet a l'administrateur de creer de nouveaux "
        "comptes, modifier les informations et les roles, et desactiver des comptes. "
        "Les mots de passe sont haches lors de la creation et de la modification."
    )

    pdf.section_title("5.6 Systeme de commentaires")
    pdf.body_text(
        "Chaque bug dispose d'un systeme de commentaires permettant aux differents "
        "participants de discuter du bug. Les commentaires sont affiches dans "
        "l'ordre chronologique et enregistrent l'auteur, le contenu et la date."
    )

    pdf.section_title("5.7 Upload de captures d'ecran")
    pdf.body_text(
        "Les utilisateurs peuvent joindre une capture d'ecran lors de la creation "
        "d'un bug. Le systeme accepte les formats JPG, PNG et WEBP avec une taille "
        "maximale de 2 Mo."
    )
    pdf.body_text(
        "Les fichiers sont stockes dans le repertoire var/uploads/screenshots/ "
        "avec un nom de fichier genere aleatoirement (hash MD5) pour eviter les "
        "collisions. Les fichiers ne sont jamais accessibles directement depuis le "
        "web ; ils sont servis via une route protegee qui verifie les droits d'acces."
    )

    # ── CHAPTER 6 ──
    pdf.add_page()
    pdf.chapter_title(6, "Securite")

    pdf.section_title("6.1 Authentification et hachage des mots de passe")
    pdf.body_text(
        "Les mots de passe sont stockes de maniere securisee grace au composant "
        "PasswordHasher de Symfony. L'algorithme auto utilise Argon2i ou BCrypt "
        "selon les capacites du systeme, avec un cout adaptatif pour empecher les "
        "attaques par force brute."
    )
    pdf.body_text(
        "Aucun mot de passe n'est jamais stocke en clair. Le hachage est effectue "
        "lors de la creation et de la modification d'un utilisateur."
    )

    pdf.section_title("6.2 Controle d'acces base sur les roles")
    pdf.body_text(
        "Le systeme de securite implemente une hierarchie de roles :"
    )
    pdf.bullet("ROLE_ADMIN : acces complet, herite de ROLE_DEVELOPER, ROLE_CLIENT et ROLE_USER")
    pdf.bullet("ROLE_DEVELOPER : gestion des bugs assignes, herite de ROLE_USER")
    pdf.bullet("ROLE_CLIENT : creation et consultation de ses propres bugs")
    pdf.bullet("ROLE_USER : acces de base a l'application")
    pdf.body_text(
        "Les regles d'acces sont definies dans security.yaml et appliquees via "
        "les attributs #[IsGranted] sur les controleurs. Le UserChecker verifie "
        "egalement que le compte est actif avant chaque requete."
    )

    pdf.section_title("6.3 Protection CSRF")
    pdf.body_text(
        "La protection CSRF est implementee via le composant CSRF de Symfony avec "
        "le mecanisme de double-submit cookie. Un controleur JavaScript Stimulus "
        "gere l'envoi des tokens CSRF lors des soumissions de formulaires via "
        "Hotwired Turbo."
    )
    pdf.body_text(
        "Tous les formulaires de suppression (bugs, projets, utilisateurs) et le "
        "formulaire de login sont proteges par des tokens CSRF valides cote serveur."
    )

    pdf.section_title("6.4 Validation des entrees")
    pdf.body_text(
        "Toutes les donnees utilisateur sont validees via les contraintes Doctrine "
        "et Symfony Validator : NotBlank, Email, Length, UniqueEntity, etc. Les "
        "types de formulaire Symfony assurent une validation supplementaire avant "
        "tout traitement metier."
    )
    pdf.body_text(
        "Les parametres de requete sont filtres et casts avant utilisation. Les "
        "identifiants numeriques sont verifies avec ctype_digit(). Les dates sont "
        "parsees avec un format strict (Y-m-d) et validees."
    )

    # ── CHAPTER 7 ──
    pdf.add_page()
    pdf.chapter_title(7, "Tests")

    pdf.section_title("7.1 Tests unitaires")
    pdf.body_text(
        "Les tests unitaires couvrent le comportement des entites metier, en "
        "particulier le mecanisme de timestamps automatiques sur BugReport. "
        "Le fichier BugReportTest.php teste :"
    )
    pdf.bullet("La mise a jour automatique de closedAt lors du changement de statut")
    pdf.bullet("La reinitialisation de closedAt lors de la reouverture d'un bug")

    pdf.section_title("7.2 Tests fonctionnels")
    pdf.body_text(
        "Les tests fonctionnels utilisent le composant BrowserKit de Symfony pour "
        "simuler des requetes HTTP. Le fichier BugReportControllerTest.php verifie :"
    )
    pdf.bullet("La redirection vers /login pour les utilisateurs non authentifies")
    pdf.body_text(
        "La suite de tests peut etre etendue pour couvrir les scenarios suivants :"
    )
    pdf.bullet("Authentification reussie et echouee")
    pdf.bullet("Creation d'un bug par un client")
    pdf.bullet("Mise a jour du statut par un developpeur")
    pdf.bullet("Gestion des projets par un administrateur")
    pdf.bullet("Restriction d'acces selon les roles")

    pdf.body_text(
        "La commande pour executer les tests : php bin/phpunit"
    )
    pdf.body_text(
        "Resultat actuel : 3 tests, 6 assertions, tous en succes."
    )

    # ── CONCLUSION ──
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 18)
    pdf.set_text_color(31, 95, 224)
    pdf.cell(0, 12, "Conclusion generale", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)
    pdf.body_text(
        "Ce stage m'a permis de developper une application web complete en utilisant "
        "le framework Symfony 7.4. J'ai pu appliquer les concepts theoriques appris "
        "en formation : architecture MVC, programmation orientee objet, base de "
        "donnees relationnelles, securite web, et tests logiciels."
    )
    pdf.body_text(
        "Les principaux apprentissages de ce stage sont :"
    )
    pdf.bullet("La maitrise du framework Symfony et son ecosysteme (Doctrine, Twig, Security)")
    pdf.bullet("La conception et l'implementation d'un modele de donnees relationnel")
    pdf.bullet("La gestion des droits d'acces base sur les roles (RBAC)")
    pdf.bullet("La protection contre les vulnerabilites courantes (CSRF, injection, XSS)")
    pdf.bullet("L'importance des tests pour garantir la fiabilite du code")
    pdf.bullet("La gestion de projet et l'organisation du code source")
    pdf.body_text(
        "Cette plateforme constitue une base solide qui peut etre etendue avec des "
        "fonctionnalites additionnelles telles que les notifications par email, "
        "l'export de rapports, ou une API REST pour l'integration avec d'autres outils."
    )

    # ── ANNEXES ──
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 18)
    pdf.set_text_color(31, 95, 224)
    pdf.cell(0, 12, "Annexes", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)

    pdf.section_title("Annexe A : Comptes de test")
    cols6 = ["Role", "Email", "Mot de passe"]
    widths6 = [40, 55, 55]
    pdf.table_header(cols6, widths6)
    accounts = [
        ["Admin", "admin@example.com", "admin"],
        ["Developpeur", "dev@example.com", "password123"],
        ["Client", "client@example.com", "password123"],
    ]
    for i, row in enumerate(accounts):
        pdf.table_row(row, widths6, fill=(i % 2 == 0))

    pdf.ln(8)
    pdf.section_title("Annexe B : Projets de test")
    cols7 = ["Nom", "Plateforme"]
    widths7 = [80, 50]
    pdf.table_header(cols7, widths7)
    projects = [
        ["Company Website", "Web"],
        ["Mobile Ordering App", "Mobile"],
        ["Internal CRM", "Web"],
        ["Partner API", "API"],
    ]
    for i, row in enumerate(projects):
        pdf.table_row(row, widths7, fill=(i % 2 == 0))

    pdf.ln(8)
    pdf.section_title("Annexe C : Routes principales")
    cols8 = ["Route", "Methode", "Description"]
    widths8 = [55, 30, 85]
    pdf.table_header(cols8, widths8)
    routes = [
        ["/", "GET", "Redirection basee sur le role"],
        ["/login", "GET|POST", "Page de connexion"],
        ["/admin/dashboard", "GET", "Tableau de bord admin"],
        ["/developer/dashboard", "GET", "Tableau de bord dev"],
        ["/client/dashboard", "GET", "Tableau de bord client"],
        ["/bugs", "GET", "Liste des bugs (filtres)"],
        ["/bugs/new", "GET|POST", "Creation d'un bug"],
        ["/bugs/{id}", "GET|POST", "Detail d'un bug"],
        ["/bugs/{id}/manage", "GET|POST", "Gestion admin d'un bug"],
        ["/bugs/{id}/status", "POST", "Mise a jour du statut"],
        ["/bugs/{id}/delete", "POST", "Suppression d'un bug"],
        ["/admin/projects", "GET", "Liste des projets"],
        ["/admin/users", "GET", "Liste des utilisateurs"],
    ]
    for i, row in enumerate(routes):
        pdf.table_row(row, widths8, fill=(i % 2 == 0))

    pdf.ln(8)
    pdf.section_title("Annexe D : Dependances principales")
    pdf.body_text(
        "doctrine/doctrine-bundle ^3.2, doctrine/doctrine-migrations-bundle ^4.0, "
        "doctrine/orm ^3.6, symfony/asset 7.4.*, symfony/asset-mapper 7.4.*, "
        "symfony/console 7.4.*, symfony/dotenv 7.4.*, symfony/flex ^2, "
        "symfony/form 7.4.*, symfony/framework-bundle 7.4.*, "
        "symfony/monolog-bundle ^3.0|^4.0, symfony/property-access 7.4.*, "
        "symfony/property-info 7.4.*, symfony/runtime 7.4.*, "
        "symfony/security-bundle 7.4.*, symfony/stimulus-bundle ^3.2, "
        "symfony/string 7.4.*, symfony/translation 7.4.*, "
        "symfony/twig-bundle 7.4.*, symfony/ux-turbo ^3.2, "
        "symfony/validator 7.4.*, symfony/yaml 7.4.*, "
        "twig/extra-bundle ^2.12|^3.0, twig/twig ^2.12|^3.0"
    )

    # ── SAVE ──
    out_path = os.path.abspath(OUTPUT)
    pdf.output(out_path)
    print(f"PDF generated: {out_path}")


if __name__ == "__main__":
    build()
