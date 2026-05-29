// =========================================================
// progression.js
// Gestion de la progression de la campagne (localStorage)
// Clé isolée par utilisateur via window.PROGRESSION_USER_ID
// =========================================================

const Progression = {

    get CLE_STORAGE() {
        const uid = window.PROGRESSION_USER_ID || 'invite';
        return 'tileburner_progression_' + uid;
    },

    charger() {
        try {
            const data = localStorage.getItem(this.CLE_STORAGE);
            if (!data) return { niveauxTermines: [], scores: {} };
            const parsed = JSON.parse(data);
            return {
                niveauxTermines: parsed.niveauxTermines || [],
                scores: parsed.scores || {}
            };
        } catch (e) {
            console.warn('Progression illisible, réinitialisation', e);
            return { niveauxTermines: [], scores: {} };
        }
    },

    sauvegarder(progression) {
        try {
            localStorage.setItem(this.CLE_STORAGE, JSON.stringify(progression));
        } catch (e) {
            console.error('Impossible de sauvegarder la progression', e);
        }
    },

    /**
     * Marque un niveau comme terminé et enregistre le meilleur score
     * @param {number} niveauId
     * @param {number} score  (nombre de déplacements - plus bas = meilleur)
     */
    terminerNiveau(niveauId, score) {
        const p = this.charger();

        // Ajout dans la liste des niveaux terminés
        if (!p.niveauxTermines.includes(niveauId)) {
            p.niveauxTermines.push(niveauId);
        }

        // Mise à jour du meilleur score (plus petit = meilleur)
        if (!p.scores[niveauId] || score < p.scores[niveauId]) {
            p.scores[niveauId] = score;
        }

        this.sauvegarder(p);
        console.log(`✅ Niveau ${niveauId} terminé (score: ${score})`);
        return p;
    },

    /**
     * Vérifie si un niveau est terminé
     */
    estTermine(niveauId) {
        const p = this.charger();
        return p.niveauxTermines.includes(niveauId);
    },

    /**
     * Vérifie si un niveau est débloqué
     * Niveau 1 toujours débloqué, sinon le précédent doit être terminé
     */
    estDebloque(niveauId) {
        if (niveauId <= 1) return true;
        return this.estTermine(niveauId - 1);
    },

    /**
     * Récupère le meilleur score d'un niveau
     */
    meilleurScore(niveauId) {
        const p = this.charger();
        return p.scores[niveauId] || null;
    },

    /**
     * Réinitialise toute la progression
     */
    reset() {
        localStorage.removeItem(this.CLE_STORAGE);
    }
};

// Exposer globalement
window.Progression = Progression;
