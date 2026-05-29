// =========================================================
// niveaux-campagne.js
// Niveaux convertis depuis campagne.c
// Format attendu par tile-burner-campagne.js
// =========================================================

const NIVEAUX_CAMPAGNE = [

    // ───────────── Niveau 1 — Tutoriel ─────────────
    {
        id: 1,
        nom: "Niveau 1 — Premiers pas",
        description: "Apprends à te déplacer et à allumer toutes les cases.",
        difficulte: "Facile",
        largeur: 4, hauteur: 4,
        depart_x: 0, depart_y: 0,
        sortie_x: 3, sortie_y: 3,
        cases_speciales: []
    },

    // ───────────── Niveau 2 ─────────────
    {
        id: 2,
        nom: "Niveau 2 — Un mur en travers",
        description: "Contourne le mur pour atteindre la sortie.",
        difficulte: "Facile",
        largeur: 5, hauteur: 5,
        depart_x: 0, depart_y: 0,
        sortie_x: 4, sortie_y: 4,
        cases_speciales: [
            { x: 2, y: 2, type: 'mur' },
        ]
    },

    // ───────────── Niveau 3 ─────────────
     {
        id: 3,
        nom: "Niveau 3 — Couloir étroit",
        description: "Un seul chemin, ne te trompe pas.",
        difficulte: "Facile",
        largeur: 7, hauteur: 7,
        depart_x: 0, depart_y: 0,
        sortie_x: 6, sortie_y: 6,
        cases_speciales: [
            { x: 1, y: 0, type: 'mur' }, { x: 1, y: 1, type: 'mur' },
            { x: 1, y: 2, type: 'mur' }, { x: 1, y: 3, type: 'mur' },
            { x: 1, y: 4, type: 'mur' }, { x: 1, y: 5, type: 'mur' },
            { x: 3, y: 1, type: 'mur' }, { x: 3, y: 2, type: 'mur' },
            { x: 3, y: 3, type: 'mur' }, { x: 3, y: 4, type: 'mur' },
            { x: 3, y: 5, type: 'mur' }, { x: 3, y: 6, type: 'mur' },
            { x: 5, y: 0, type: 'mur' }, { x: 5, y: 1, type: 'mur' },
            { x: 5, y: 2, type: 'mur' }, { x: 5, y: 3, type: 'mur' },
            { x: 5, y: 4, type: 'mur' }, { x: 5, y: 5, type: 'mur' },
        ]
    },

    // ───────────── Niveau 4 ─────────────
   
    {
        id: 4,
        nom: "Niveau 4 — Dédale de murs",
        description: "Quel sera le meilleur chemin ?",
        difficulte: "Moyen",
        largeur: 7, hauteur: 7,
        depart_x: 3, depart_y: 3,
        sortie_x: 0, sortie_y: 6,
        cases_speciales: [
            { x: 1, y: 0, type: 'mur' }, { x: 1, y: 1, type: 'mur' },
            { x: 2, y: 2, type: 'mur' }, { x: 4, y: 1, type: 'mur' },
            { x: 6, y: 1, type: 'mur' }, { x: 6, y: 2, type: 'mur' },
            { x: 1, y: 4, type: 'mur' }, { x: 3, y: 4, type: 'mur' },
            { x: 6, y: 5, type: 'mur' }, { x: 1, y: 6, type: 'mur' },
            { x: 2, y: 6, type: 'mur' }
        ]
    },

    // ───────────── Niveau 5 ─────────────

    {
        id: 5,
        nom: "Niveau 5 — Glissade",
        description: "Découvre les cases de glace.",
        difficulte: "Facile",
        largeur: 5, hauteur: 5,
        depart_x: 0, depart_y: 0,
        sortie_x: 4, sortie_y: 4,
        cases_speciales: [
            { x: 1, y: 2, type: 'glace' },
            { x: 2, y: 2, type: 'glace' },
            { x: 3, y: 2, type: 'glace' },
        ]
    },


    // ───────────── Niveau 6 ─────────────

     {
        id: 6,
        nom: "Niveau 6 — Sol fragile",
        description: "Attention où tu marches, le sol peut céder.",
        difficulte: "Facile",
        largeur: 5, hauteur: 5,
        depart_x: 0, depart_y: 0,
        sortie_x: 4, sortie_y: 4,
        cases_speciales: [
            { x: 1, y: 1, type: 'fragile' },
            { x: 2, y: 2, type: 'fragile' },
            { x: 3, y: 3, type: 'fragile' },
        ]
    },

    // ───────────── Niveau 7 ─────────────
    {
        id: 7,
        nom: "Niveau 7 — Lumière maudite",
        description: "Les cases maudites ne restent allumées qu'un instant.",
        difficulte: "Moyen",
        largeur: 6, hauteur: 6,
        depart_x: 0, depart_y: 0,
        sortie_x: 5, sortie_y: 5,
        cases_speciales: [
            { x: 2, y: 2, type: 'maudite' },
            { x: 3, y: 3, type: 'maudite' },
            { x: 4, y: 2, type: 'maudite' },
        ]
    },

    // ───────────── Niveau 8 ─────────────

   {
        id: 8,
        nom: "Niveau 8 — Premier piège",
        description: "Évite le piège qui s'arme tous les 5 pas.",
        difficulte: "Moyen",
        largeur: 6, hauteur: 6,
        depart_x: 0, depart_y: 0,
        sortie_x: 5, sortie_y: 5,
        cases_speciales: [
            { x: 2, y: 3, type: 'piege' },
            { x: 4, y: 1, type: 'mur' },
            { x: 1, y: 4, type: 'mur' },
        ]
    },

    // ───────────── Niveau 9 ─────────────

     {
        id: 9,
        nom: "Niveau 9 — Téléporteurs",
        description: "Utilise les portails pour traverser la grille.",
        difficulte: "Moyen",
        largeur: 6, hauteur: 6,
        depart_x: 0, depart_y: 0,
        sortie_x: 5, sortie_y: 5,
        cases_speciales: [
            { x: 1, y: 1, type: 'teleport_a' },
            { x: 4, y: 4, type: 'teleport_b' },
        ]
    },

   
    // ───────────── Niveau 10 ─────────────
    {
        id: 10,
        nom: "Niveau 10 — Sol qui s'effondre",
        description: "Le sol cède sous tes pas, planifie ton chemin.",
        difficulte: "Difficile",
        largeur: 7, hauteur: 7,
        depart_x: 0, depart_y: 0,
        sortie_x: 6, sortie_y: 6,
        cases_speciales: [
            { x: 2, y: 0, type: 'fragile' }, { x: 2, y: 1, type: 'fragile' },
            { x: 1, y: 2, type: 'fragile' }, { x: 2, y: 2, type: 'fragile' },
            { x: 0, y: 2, type: 'fragile' },
            { x: 1, y: 3, type: 'fragile' }, { x: 2, y: 4, type: 'fragile' },
            { x: 3, y: 3, type: 'fragile' }, { x: 5, y: 0, type: 'fragile' },
            { x: 6, y: 1, type: 'fragile' },
            { x: 4, y: 4, type: 'fragile' }, { x: 1, y: 5, type: 'fragile' },
            { x: 3, y: 5, type: 'fragile' }, { x: 5, y: 5, type: 'fragile' },
            { x: 4, y: 6, type: 'fragile' },
        ]
    },

    // ───────────── Niveau 11 ─────────────
    {
        id: 11,
        nom: "Niveau 11 — Champ de pièges",
        description: "Les pièges s'activent en rythme, sois prudent.",
        difficulte: "Difficile",
        largeur: 7, hauteur: 7,
        depart_x: 0, depart_y: 0,
        sortie_x: 6, sortie_y: 6,
        cases_speciales: [
            { x: 2, y: 2, type: 'piege' },
            { x: 3, y: 1, type: 'piege' },
            { x: 4, y: 2, type: 'piege' },
            { x: 1, y: 3, type: 'piege' },
            { x: 2, y: 4, type: 'piege' },
            { x: 5, y: 3, type: 'piege' },
            { x: 4, y: 4, type: 'piege' },
            { x: 3, y: 5, type: 'piege' },
            { x: 3, y: 3, type: 'piege' },
        ]
    },

    // ───────────── Niveau 12 ─────────────
    {
        id: 12,
        nom: "Niveau 12 — Double portail",
        description: "Deux paires de portails pour traverser.",
        difficulte: "Difficile",
        largeur: 7, hauteur: 7,
        depart_x: 0, depart_y: 0,
        sortie_x: 0, sortie_y: 6,
        cases_speciales: [
            { x: 6, y: 0, type: 'teleport_a' },
            { x: 0, y: 4, type: 'teleport_b' },
            { x: 3, y: 0, type: 'mur' }, { x: 3, y: 1, type: 'mur' },
            { x: 0, y: 3, type: 'mur' }, { x: 1, y: 3, type: 'mur' },
            { x: 2, y: 3, type: 'mur' },{ x: 3, y: 3, type: 'mur' }, 
            { x: 4, y: 3, type: 'mur' },
            { x: 5, y: 3, type: 'mur' }, { x: 6, y: 3, type: 'mur' },
            { x: 2, y: 4, type: 'mur' }, { x: 3, y: 4, type: 'mur' }, 
            { x: 5, y: 5, type: 'mur' },
            { x: 4, y: 6, type: 'mur' }, { x: 5, y: 6, type: 'mur' },
            { x: 4, y: 2, type: 'glace' },
            { x: 5, y: 2, type: 'glace' },
            { x: 6, y: 2, type: 'glace' },
            { x: 3, y: 5, type: 'glace' },
            { x: 4, y: 5, type: 'glace' },
            { x: 3, y: 6, type: 'glace' },
            { x: 3, y: 2, type: 'fragile' },
            { x: 2, y: 5, type: 'piege' },
            { x: 2, y: 6, type: 'fragile' },
            { x: 6, y: 5, type: 'piege' },
            
        ]
    },

    // ───────────── Niveau 13 ─────────────
    {
        id: 13,
        nom: "Niveau 13 — Mémoire courte",
        description: "Beaucoup de cases maudites, allume vite !",
        difficulte: "Difficile",
        largeur: 7, hauteur: 7,
        depart_x: 0, depart_y: 0,
        sortie_x: 6, sortie_y: 6,
        cases_speciales: [
            { x: 1, y: 1, type: 'maudite' }, { x: 3, y: 1, type: 'maudite' },
            { x: 5, y: 1, type: 'maudite' },
            { x: 1, y: 3, type: 'maudite' }, { x: 3, y: 3, type: 'maudite' },
            { x: 5, y: 3, type: 'maudite' },
            { x: 1, y: 5, type: 'maudite' }, { x: 3, y: 5, type: 'maudite' },
            { x: 5, y: 5, type: 'maudite' },
        ]
    },

    // ───────────── Niveau 14 ─────────────
    {
        id: 14,
        nom: "Niveau 14 — La Faille Géométrique",
        description: "Regarde bien ou tu marches.",
        difficulte: "Difficile",
        largeur: 7, hauteur: 7,
        depart_x: 3, depart_y: 0,
        sortie_x: 6, sortie_y: 6,
        cases_speciales: [
            { x: 0, y: 2, type: 'mur' }, { x: 1, y: 3, type: 'mur' },
            { x: 2, y: 1, type: 'mur' }, { x: 4, y: 1, type: 'mur' },
            { x: 5, y: 3, type: 'mur' }, { x: 6, y: 2, type: 'mur' },
            { x: 2, y: 5, type: 'piege' }, { x: 1, y: 4, type: 'mur' },
            { x: 5, y: 4, type: 'fragile' },
            { x: 1, y: 1, type: 'glace' },
            { x: 2, y: 0, type: 'glace' },
            { x: 3, y: 1, type: 'glace' },
            { x: 4, y: 0, type: 'glace' },
            { x: 5, y: 1, type: 'glace' },
            { x: 3, y: 2, type: 'fragile' }, 
            { x: 1, y: 5, type: 'fragile' },
            { x: 1, y: 6, type: 'fragile' }, 
            { x: 5, y: 5, type: 'fragile' },
            { x: 5, y: 6, type: 'fragile' },
            { x: 1, y: 0, type: 'piege' },
            { x: 5, y: 0, type: 'piege' },
            { x: 2, y: 2, type: 'piege' },
            { x: 4, y: 2, type: 'piege' },
            { x: 6, y: 5, type: 'piege' },
            { x: 0, y: 5, type: 'maudite' },
        ]
    },

    // ───────────── Niveau 15 ─────────────
    {
        id: 15,
        nom: "Niveau 15 — La salle maudite",
        description: "Compte bien le nombre de pas.",
        difficulte: "Difficile",
        largeur: 8, hauteur: 8,
        depart_x: 6, depart_y: 5,
        sortie_x: 2, sortie_y: 2,
        cases_speciales: [
            { x: 4, y: 2, type: 'mur' }, { x: 5, y: 2, type: 'mur' },
            { x: 1, y: 4, type: 'mur' }, { x: 5, y: 6, type: 'mur' },
            { x: 0, y: 1, type: 'glace' },
            { x: 0, y: 2, type: 'glace' },
            { x: 0, y: 3, type: 'glace' },
            { x: 0, y: 4, type: 'glace' },
            { x: 2, y: 4, type: 'glace' },
            { x: 3, y: 4, type: 'glace' },
            { x: 4, y: 4, type: 'glace' },
            { x: 5, y: 5, type: 'glace' },
            { x: 7, y: 4, type: 'glace' },
            { x: 1, y: 0, type: 'maudite' },
            { x: 0, y: 5, type: 'maudite' },
            { x: 2, y: 6, type: 'maudite' },
            { x: 7, y: 3, type: 'maudite' },
            { x: 6, y: 4, type: 'maudite' },
            { x: 3, y: 1, type: 'teleport_a' },
            { x: 4, y: 5, type: 'teleport_b' }, 
            { x: 0, y: 0, type: 'piege' },
            { x: 4, y: 3, type: 'piege' },
            { x: 5, y: 7, type: 'piege' },
            { x: 5, y: 0, type: 'fragile' }, 
            { x: 5, y: 1, type: 'fragile' },
            { x: 6, y: 2, type: 'fragile' }, 
        ]
    },

    // ───────────── Niveau 16 ─────────────
    {
        id: 16,
        nom: "Niveau 16 — Ice Mazing",
        description: "Tous les mécanismes du jeu sont réunis.",
        difficulte: "Difficile",
        largeur: 8, hauteur: 8,
        depart_x: 0, depart_y: 0,
        sortie_x: 4, sortie_y: 3,
        cases_speciales: [
            { x: 1, y: 0, type: 'glace' },
            { x: 2, y: 0, type: 'glace' },
            { x: 3, y: 0, type: 'glace' },
            { x: 4, y: 0, type: 'glace' },
            { x: 5, y: 0, type: 'glace' },
            { x: 0, y: 1, type: 'glace' },
            { x: 1, y: 1, type: 'glace' },
            { x: 7, y: 0, type: 'glace' },
            { x: 2, y: 3, type: 'glace' },
            { x: 3, y: 3, type: 'glace' },
            { x: 6, y: 3, type: 'glace' },
            { x: 7, y: 3, type: 'glace' },
            { x: 0, y: 4, type: 'glace' },
            { x: 1, y: 4, type: 'glace' },
            { x: 2, y: 4, type: 'glace' },
            { x: 3, y: 4, type: 'glace' },
            { x: 6, y: 4, type: 'glace' },
            { x: 7, y: 4, type: 'glace' },
            { x: 0, y: 5, type: 'glace' },
            { x: 1, y: 5, type: 'glace' },
            { x: 2, y: 5, type: 'glace' },
            { x: 4, y: 5, type: 'glace' },
            { x: 5, y: 5, type: 'glace' },
            { x: 6, y: 5, type: 'glace' },
            { x: 7, y: 5, type: 'glace' },
            { x: 0, y: 6, type: 'glace' },
            { x: 1, y: 6, type: 'glace' },
            { x: 2, y: 6, type: 'glace' },
            { x: 3, y: 6, type: 'glace' },
            { x: 4, y: 6, type: 'glace' },
            { x: 5, y: 6, type: 'glace' },
            { x: 6, y: 6, type: 'glace' },
            { x: 1, y: 7, type: 'glace' },
            { x: 2, y: 7, type: 'glace' },
            { x: 3, y: 7, type: 'glace' },
            { x: 5, y: 7, type: 'glace' },
            { x: 6, y: 7, type: 'glace' },
            { x: 2, y: 1, type: 'glace' },
            { x: 3, y: 1, type: 'glace' },
            { x: 5, y: 1, type: 'glace' },
            { x: 6, y: 1, type: 'glace' },
            { x: 7, y: 1, type: 'glace' },
            { x: 0, y: 2, type: 'glace' },
            { x: 1, y: 2, type: 'glace' },
            { x: 2, y: 2, type: 'glace' },
            { x: 3, y: 2, type: 'glace' },
            { x: 4, y: 2, type: 'glace' },
            { x: 6, y: 2, type: 'glace' },
            { x: 7, y: 2, type: 'glace' },
            { x: 6, y: 0, type: 'mur' },
            { x: 5, y: 2, type: 'mur' },
            { x: 5, y: 3, type: 'mur' },
            { x: 5, y: 4, type: 'mur' },
            { x: 4, y: 4, type: 'mur' },
            { x: 3, y: 5, type: 'mur' },
            { x: 4, y: 7, type: 'mur' },
            { x: 7, y: 6, type: 'mur' },
            { x: 0, y: 3, type: 'mur' },
            { x: 0, y: 7, type: 'maudite' },
            { x: 4, y: 1, type: 'maudite' },
        ]
    },

    // ───────────── Niveau 17 ─────────────
    {
        id: 17,
        nom: "Niveau 17 — Les Vestiges du Brasier",
        description: "Ne te prends pas trop la tête.",
        difficulte: "Difficile",
        largeur: 8, hauteur:8,
        depart_x: 0, depart_y: 0,
        sortie_x: 7, sortie_y: 7,
        cases_speciales: [

            { x: 2, y: 0, type: 'mur' }, { x: 7, y: 0, type: 'mur' },
            { x: 4, y: 1, type: 'mur' },
            { x: 0, y: 2, type: 'mur' },
            { x: 2, y: 3, type: 'mur' }, { x: 3, y: 3, type: 'mur' }, { x: 5, y: 3, type: 'mur' },
            { x: 6, y: 4, type: 'mur' },
            { x: 1, y: 5, type: 'mur' }, { x: 3, y: 5, type: 'mur' },
            { x: 4, y: 6, type: 'mur' }, { x: 6, y: 6, type: 'mur' },
            { x: 0, y: 7, type: 'mur' },
            { x: 4, y: 0, type: 'glace' },
            { x: 6, y: 1, type: 'glace' },
            { x: 0, y: 3, type: 'glace' },
            { x: 7, y: 4, type: 'glace' },
            { x: 2, y: 6, type: 'glace' },
            { x: 2, y: 2, type: 'fragile' },
            { x: 5, y: 4, type: 'fragile' },
            { x: 0, y: 6, type: 'fragile' },
            { x: 3, y: 7, type: 'fragile' },
            { x: 7, y: 2, type: 'piege' },
            { x: 1, y: 4, type: 'piege' },
            { x: 5, y: 6, type: 'piege' },
            { x: 5, y: 2, type: 'teleport_a' },
            { x: 6, y: 5, type: 'teleport_b' },
            { x: 6, y: 0, type: 'maudite' },
            { x: 3, y: 4, type: 'maudite' },
        ]
    },

    // ───────────── Niveau 18 ─────────────
    {
        id: 18,
        nom: "Niveau 18 — Le Labyrinthe",
        description: "Ne vous perdez pas en route.",
        difficulte: "Difficile",
        largeur: 10, hauteur: 10,
        depart_x: 0, depart_y: 0,
        sortie_x: 9, sortie_y: 9,
        cases_speciales: [
            { x: 2, y: 0, type: 'mur' },
            { x: 6, y: 0, type: 'mur' },
            { x: 0, y: 1, type: 'mur' },
            { x: 2, y: 1, type: 'mur' },
            { x: 4, y: 1, type: 'mur' },
            { x: 6, y: 1, type: 'mur' },
            { x: 8, y: 1, type: 'mur' },
            { x: 4, y: 2, type: 'mur' },
            { x: 8, y: 2, type: 'mur' },
            { x: 1, y: 3, type: 'mur' },
            { x: 2, y: 3, type: 'mur' },
            { x: 3, y: 3, type: 'mur' },
            { x: 4, y: 3, type: 'mur' },
            { x: 5, y: 3, type: 'mur' },
            { x: 6, y: 3, type: 'mur' },
            { x: 7, y: 3, type: 'mur' },
            { x: 8, y: 3, type: 'mur' },
            { x: 3, y: 4, type: 'mur' },
            { x: 8, y: 4, type: 'mur' },
            { x: 0, y: 5, type: 'mur' },
            { x: 1, y: 5, type: 'mur' },
            { x: 3, y: 5, type: 'mur' },
            { x: 5, y: 5, type: 'mur' },
            { x: 6, y: 5, type: 'mur' },
            { x: 8, y: 5, type: 'mur' },
            { x: 9, y: 5, type: 'mur' },
            { x: 5, y: 6, type: 'mur' },
            { x: 1, y: 7, type: 'mur' },
            { x: 2, y: 7, type: 'mur' },
            { x: 3, y: 7, type: 'mur' },
            { x: 4, y: 7, type: 'mur' },
            { x: 5, y: 7, type: 'mur' },
            { x: 7, y: 7, type: 'mur' },
            { x: 8, y: 7, type: 'mur' },
            { x: 1, y: 8, type: 'mur' },
            { x: 7, y: 8, type: 'mur' },
            { x: 1, y: 9, type: 'mur' },
            { x: 3, y: 9, type: 'mur' },
            { x: 4, y: 9, type: 'mur' },
            { x: 5, y: 9, type: 'mur' },
            { x: 6, y: 9, type: 'mur' },
            { x: 7, y: 9, type: 'mur' },
            { x: 8, y: 9, type: 'mur' },
            { x: 2, y: 2, type: 'piege' }, 
            { x: 6, y: 2, type: 'piege' }, 
            { x: 5, y: 4, type: 'piege' }, 
            { x: 1, y: 6, type: 'piege' }, 
            { x: 5, y: 8, type: 'piege' }, 
            { x: 9, y: 8, type: 'piege' }
            ]
    },
    // ───────────── Niveau 19 ─────────────
    {
        id: 19,
        nom: "Niveau 19 — Téléport infernal",
        description: "Plusieurs portails .",
        difficulte: "Difficile",
        largeur: 10, hauteur: 10,
        depart_x: 0, depart_y: 0,
        sortie_x: 9, sortie_y: 9,
        cases_speciales: [
           { x: 3, y: 0, type: 'mur' }, { x: 6, y: 0, type: 'mur' },
            { x: 3, y: 2, type: 'mur' }, { x: 6, y: 2, type: 'mur' },
            { x: 0, y: 3, type: 'mur' }, { x: 9, y: 3, type: 'mur' },
            { x: 0, y: 6, type: 'mur' }, { x: 9, y: 6, type: 'mur' },
            { x: 3, y: 7, type: 'mur' }, { x: 6, y: 7, type: 'mur' },
            { x: 2, y: 9, type: 'mur' }, { x: 3, y: 9, type: 'mur' }, { x: 6, y: 9, type: 'mur' }, { x: 7, y: 9, type: 'mur' },
            { x: 1, y: 1, type: 'glace' }, { x: 2, y: 1, type: 'glace' }, { x: 3, y: 1, type: 'glace' },
            { x: 6, y: 1, type: 'glace' }, { x: 7, y: 1, type: 'glace' }, { x: 8, y: 1, type: 'glace' },
            { x: 1, y: 8, type: 'glace' }, { x: 2, y: 8, type: 'glace' }, { x: 3, y: 8, type: 'glace' },
            { x: 6, y: 8, type: 'glace' }, { x: 7, y: 8, type: 'glace' }, { x: 8, y: 8, type: 'glace' },
            { x: 4, y: 3, type: 'fragile' }, { x: 5, y: 3, type: 'fragile' },
            { x: 4, y: 6, type: 'fragile' }, { x: 5, y: 6, type: 'fragile' },
            { x: 4, y: 4, type: 'piege' }, { x: 5, y: 4, type: 'piege' },
            { x: 4, y: 5, type: 'piege' }, { x: 5, y: 5, type: 'piege' },
            { x: 1, y: 5, type: 'teleport_a' }, { x: 8, y: 2, type: 'teleport_b' }, 
            { x: 9, y: 7, type: 'teleport_a' }, 
            { x: 0, y: 7, type: 'maudite' }, 
            { x: 0, y: 4, type: 'maudite' }, 
            { x: 9, y: 0, type: 'maudite' }
        ]
    },

    // ───────────── Niveau 20 — Final ─────────────
    {
        id: 20,
        nom: "Niveau 20 — Maître du feu",
        description: "Le défi ultime de la campagne.",
        difficulte: "Expert",
        largeur: 8, hauteur: 8,
        depart_x: 0, depart_y: 0,
        sortie_x: 3, sortie_y: 5,
        cases_speciales: [
            { x: 3, y: 0, type: 'mur' }, { x: 5, y: 0, type: 'mur' },
            { x: 5, y: 2, type: 'mur' }, { x: 1, y: 4, type: 'mur' },
            { x: 6, y: 5, type: 'mur' }, { x: 3, y: 6, type: 'mur' },
            { x: 4, y: 1, type: 'glace' }, { x: 0, y: 3, type: 'glace' },
            { x: 7, y: 4, type: 'glace' }, { x: 1, y: 6, type: 'glace' },
            { x: 1, y: 7, type: 'glace' },{ x: 4, y: 5, type: 'glace' },
            { x: 7, y: 2, type: 'fragile' }, { x: 3, y: 4, type: 'fragile' },
            { x: 5, y: 6, type: 'fragile' },
            { x: 5, y: 7, type: 'fragile' },
            { x: 6, y: 1, type: 'piege' }, { x: 0, y: 5, type: 'piege' },
            { x: 7, y: 6, type: 'piege' },
            { x: 4, y: 3, type: 'teleport_a' },
            { x: 7, y: 1, type: 'teleport_b' },
            { x: 1, y: 1, type: 'maudite' },
            { x: 2, y: 5, type: 'maudite' },
            { x: 6, y: 3, type: 'maudite' },
        ]
    },
];

// Expose globalement
if (typeof window !== 'undefined') {
    window.NIVEAUX_CAMPAGNE = NIVEAUX_CAMPAGNE;
}

// Aide : récupérer un niveau par son id
if (typeof window !== 'undefined') {
    window.getNiveauCampagne = function(id) {
        return NIVEAUX_CAMPAGNE.find(n => n.id === parseInt(id)) || null;
    };
    window.NIVEAUX_CAMPAGNE = NIVEAUX_CAMPAGNE;
}