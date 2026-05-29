// =========================================================
// tile-burner-campagne.js
// Moteur de jeu avec cases spéciales :
//   - ETEINTE / ALLUMEE / MUR / SORTIE
//   - GLACE       : on glisse jusqu'à un obstacle
//   - FRAGILE     : devient un TROU après passage
//   - TROU        : infranchissable (comme un mur)
//   - PIEGE       : s'active tous les N déplacements -> reset niveau
//   - TELEPORT_A / TELEPORT_B : téléporte vers le portail jumeau
//   - MAUDITE     : reste allumée X déplacements puis s'éteint
// =========================================================

const TYPE = {
  ETEINTE: 0,
  ALLUMEE: 1,
  MUR: 2,
  SORTIE: 3,
  GLACE: 4,
  FRAGILE: 5,
  TROU: 6,
  PIEGE: 7,
  TELEPORT_A: 8,
  TELEPORT_B: 9,
  MAUDITE_OFF: 10,
  MAUDITE_ON: 11,
};

class TileBurnerCampagne {
  constructor(canvas, config, callbacks = {}) {
    this.canvas = canvas;
    this.ctx = canvas.getContext("2d");
    this.callbacks = callbacks;
    this.configInitiale = JSON.parse(JSON.stringify(config));

    // Paramètres des cases spéciales (modifiables via config)
    this.piegeIntervalle = config.piege_intervalle ?? 5; // tous les N déplacements
    this.mauditeDuree = config.maudite_duree ?? 20; // durée en déplacements

    // Chargement images
    this.images = {};
    const skinActif = window.SkinSecret && window.SkinSecret.estActif();
    const suffixe = skinActif ? "_secret" : "";

    const assets = {
      eteinte: "../img/asset/case_eteinte.png",
      allumee: "../img/asset/case_allumee.png",
      mur1: "../img/asset/mur1.png",
      mur2: "../img/asset/mur2.png",
      mur3: "../img/asset/mur3.png",
      mur4: "../img/asset/mur4.png",
      mur5: "../img/asset/mur5.png",
      sortie: "../img/asset/sortie.png",
      glace: "../img/asset/glace.png",
      fragile: "../img/asset/fragile.png",
      trou: "../img/asset/trou.png",
      piege_off: "../img/asset/piege_off.png",
      piege_on: "../img/asset/piege_on.png",
      teleport_a: "../img/asset/teleport_a.png",
      teleport_b: "../img/asset/teleport_b.png",
      maudite_on: "../img/asset/maudite_on.png",
      maudite_off: "../img/asset/maudite_off.png",
      joueur_haut: `../img/asset/joueur${suffixe}_haut.png`,
      joueur_bas: `../img/asset/joueur${suffixe}_bas.png`,
      joueur_gauche: `../img/asset/joueur${suffixe}_gauche.png`,
      joueur_droite: `../img/asset/joueur${suffixe}_droite.png`,
    };

    if (skinActif) {
      assets.joueur_haut_marche = "../img/asset/joueur_secret_haut_marche.png";
      assets.joueur_bas_marche = "../img/asset/joueur_secret_bas_marche.png";
      assets.joueur_gauche_marche =
        "../img/asset/joueur_secret_gauche_marche.png";
      assets.joueur_droite_marche =
        "../img/asset/joueur_secret_droite_marche.png";
    }

    this._assetsCharges = 0;
    this._assetsTotal = Object.keys(assets).length;

    for (const [nom, src] of Object.entries(assets)) {
      const img = new Image();
      img.onload = () => {
        this._assetsCharges++;
        if (this._assetsCharges === this._assetsTotal) {
          this.dessiner();
        }
      };
      img.onerror = () => {
        this._assetsCharges++;
      };
      img.src = src;
      this.images[nom] = img;
    }

    // Animation
    this.animation = null;
    this.direction = "bas";
    this.dureeAnimation = 140; // ms par case (sera réduit pour la glace)

    this._initialiser(config);
  }

  // ===========================================================
  // INITIALISATION
  // ===========================================================
  _initialiser(c) {
    this.largeur = c.largeur || c.colonnes || 8;
    this.hauteur = c.hauteur || c.lignes || 8;

    // Grille vide
    this.grille = [];
    for (let y = 0; y < this.hauteur; y++) {
      const ligne = [];
      for (let x = 0; x < this.largeur; x++) ligne.push(TYPE.ETEINTE);
      this.grille.push(ligne);
    }

    this.varianteMurs = {};

    // Murs simples (compat)
    if (Array.isArray(c.murs)) {
      for (const m of c.murs) {
        const mx = m[0],
          my = m[1];
        if (this._dansGrille(mx, my)) {
          this.grille[my][mx] = TYPE.MUR;
          this.varianteMurs[`${mx},${my}`] = 1 + Math.floor(Math.random() * 5);
        }
      }
    }

    // Cases spéciales : tableau d'objets { x, y, type }
    // type peut être une string ("glace", "fragile", ...) ou une valeur TYPE
    if (Array.isArray(c.cases_speciales)) {
      for (const cs of c.cases_speciales) {
        if (!this._dansGrille(cs.x, cs.y)) continue;
        const t = this._normaliserType(cs.type);
        this.grille[cs.y][cs.x] = t;
        if (t === TYPE.MUR) {
          this.varianteMurs[`${cs.x},${cs.y}`] =
            1 + Math.floor(Math.random() * 5);
        }
      }
    }

    // Sortie
    if (
      c.sortie_x !== undefined &&
      c.sortie_y !== undefined &&
      this._dansGrille(c.sortie_x, c.sortie_y)
    ) {
      this.sortie = { x: c.sortie_x, y: c.sortie_y };
      this.grille[c.sortie_y][c.sortie_x] = TYPE.SORTIE;
    } else {
      this.sortie = null;
    }

    // Joueur
    const dx = c.depart_x ?? (c.depart ? c.depart[0] : 0);
    const dy = c.depart_y ?? (c.depart ? c.depart[1] : 0);
    this.joueur = { x: dx, y: dy };
    this.depart = { x: dx, y: dy };

    // La case de départ s'allume si éteinte
    if (this.grille[dy][dx] === TYPE.ETEINTE) {
      this.grille[dy][dx] = TYPE.ALLUMEE;
    }

    // Compteurs / état
    this.nbDeplacements = 0;
    this.compteurPas = 0;
    this.historique = [];
    this.termine = false;

    // État dynamique des maudites : map "x,y" -> nb déplacements restants
    this.mauditeTimers = {};
    // Cases maudites visitées au moins une fois (pour la condition de victoire)
    this.mauditesVisitees = new Set();

    // Téléporteurs : on indexe les positions A et B
    this.teleports = { A: null, B: null };
    for (let y = 0; y < this.hauteur; y++) {
      for (let x = 0; x < this.largeur; x++) {
        if (this.grille[y][x] === TYPE.TELEPORT_A) this.teleports.A = { x, y };
        if (this.grille[y][x] === TYPE.TELEPORT_B) this.teleports.B = { x, y };
      }
    }

    this._calculerTotalAllumables();
    this._redimensionnerCanvas();
    this._notifierMaj();
    this.dessiner();
  }

  _normaliserType(t) {
    if (typeof t === "number") return t;
    const map = {
      eteinte: TYPE.ETEINTE,
      allumee: TYPE.ALLUMEE,
      mur: TYPE.MUR,
      sortie: TYPE.SORTIE,
      glace: TYPE.GLACE,
      fragile: TYPE.FRAGILE,
      trou: TYPE.TROU,
      piege: TYPE.PIEGE,
      teleport_a: TYPE.TELEPORT_A,
      teleporta: TYPE.TELEPORT_A,
      teleport_b: TYPE.TELEPORT_B,
      teleportb: TYPE.TELEPORT_B,
      maudite: TYPE.MAUDITE_OFF,
      maudite_off: TYPE.MAUDITE_OFF,
      maudite_on: TYPE.MAUDITE_ON,
    };
    return map[String(t).toLowerCase()] ?? TYPE.ETEINTE;
  }

  _dansGrille(x, y) {
    return x >= 0 && y >= 0 && x < this.largeur && y < this.hauteur;
  }

  _redimensionnerCanvas() {
    // garder un canvas carré ; taille de case = min(600/largeur, 600/hauteur)
    const taille = Math.floor(600 / Math.max(this.largeur, this.hauteur));
    this.tailleCase = taille;
    this.canvas.width = taille * this.largeur;
    this.canvas.height = taille * this.hauteur;
  }

  // ===========================================================
  // CASES "ALLUMABLES"
  // ===========================================================
  _estAllumable(t) {
    return (
      t === TYPE.ETEINTE ||
      t === TYPE.ALLUMEE ||
      t === TYPE.FRAGILE ||
      t === TYPE.MAUDITE_OFF ||
      t === TYPE.MAUDITE_ON
    );
  }

  _calculerTotalAllumables() {
    let n = 0;
    for (let y = 0; y < this.hauteur; y++) {
      for (let x = 0; x < this.largeur; x++) {
        if (this._estAllumable(this.grille[y][x])) n++;
      }
    }
    // on inclut aussi les maudites (qui basculent ON/OFF)
    // mais elles ne comptent pas dans le total à allumer
    this.totalAllumables = n;
  }

  _compterAllumees() {
    let n = 0;
    for (let y = 0; y < this.hauteur; y++) {
      for (let x = 0; x < this.largeur; x++) {
        const t = this.grille[y][x];
        if (t === TYPE.ALLUMEE) n++;
        // Une maudite compte comme allumée si elle a été visitée au moins une fois
        else if (
          (t === TYPE.MAUDITE_ON || t === TYPE.MAUDITE_OFF) &&
          this.mauditesVisitees.has(`${x},${y}`)
        ) n++;
      }
    }
    return n;
  }

  // ===========================================================
  // DÉPLACEMENT
  // ===========================================================
  deplacer(dy, dx) {
    if (this.termine || this.animation) return;

    const nx = this.joueur.x + dx;
    const ny = this.joueur.y + dy;

    if (!this._dansGrille(nx, ny)) return;

    const cible = this.grille[ny][nx];

    // Cases bloquantes
    if (cible === TYPE.MUR || cible === TYPE.TROU) return;

    // Direction
    if (dx === 1) this.direction = "droite";
    else if (dx === -1) this.direction = "gauche";
    else if (dy === 1) this.direction = "bas";
    else if (dy === -1) this.direction = "haut";

    // Snapshot pour undo
    this._sauvegarderEtat();

    const oldX = this.joueur.x;
    const oldY = this.joueur.y;
    const oldType = this.grille[oldY][oldX];

    // ---- Effet sur la case QUITTÉE ----
    // Si on quitte une case FRAGILE -> elle devient TROU
    if (oldType === TYPE.FRAGILE) {
      this.grille[oldY][oldX] = TYPE.TROU;
      this.totalAllumables--; // une case allumable de moins
    }

    // ---- Effet sur la case CIBLE (allumer/éteindre) ----
    this._appliquerEntreeCase(nx, ny);

    // Déplacement officiel
    this.joueur.x = nx;
    this.joueur.y = ny;
    this.nbDeplacements++;
    this.compteurPas++;

    // Notifier le HUD immédiatement (avant tout return spécial)
    if (this.callbacks.onDeplacement)
      this.callbacks.onDeplacement(this.nbDeplacements);

    // ---- Tick : maudites + pièges ----
    this._tickMaudites();
    const piegeDeclenche = this._tickPieges(nx, ny);

    if (piegeDeclenche) {
      // Animation puis reset
      this._lancerAnimation(oldX, oldY, nx, ny, () => {
        if (this.callbacks.onMessage)
          this.callbacks.onMessage("💥 Piège déclenché !");
        this.recommencer();
      });
      return;
    }

    // ---- Téléporteur ? ----
    if (cible === TYPE.TELEPORT_A || cible === TYPE.TELEPORT_B) {
      const dest =
        cible === TYPE.TELEPORT_A ? this.teleports.B : this.teleports.A;
      if (dest && (dest.x !== nx || dest.y !== ny)) {
        this._lancerAnimation(oldX, oldY, nx, ny, () => {
          this.joueur.x = dest.x;
          this.joueur.y = dest.y;
          this._appliquerEntreeCase(dest.x, dest.y);
          this._notifierMaj();
          this.dessiner();
          this._verifierVictoire(cible);
        });
        return;
      }
    }

    // ---- Glace : on glisse ----
    if (cible === TYPE.GLACE) {
      this._lancerAnimation(
        oldX,
        oldY,
        nx,
        ny,
        () => {
          this._glisser(dy, dx);
        },
        80,
      ); // animation plus rapide
      return;
    }

    // ---- Victoire ? ----
    if (this._verifierVictoireImmediat(cible, oldX, oldY, nx, ny)) return;

    this._notifierMaj();
    this._lancerAnimation(oldX, oldY, nx, ny);
  }

  // Applique allumage/extinction sur la case d'arrivée
  _appliquerEntreeCase(x, y) {
    const t = this.grille[y][x];
    if (t === TYPE.ETEINTE) this.grille[y][x] = TYPE.ALLUMEE;
    else if (t === TYPE.ALLUMEE) this.grille[y][x] = TYPE.ETEINTE;
    else if (t === TYPE.FRAGILE) {
      // On l'allume au passage ; deviendra trou quand on partira
      // On la traite comme une allumée temporaire
      // (le passage de FRAGILE -> TROU est géré en sortie de case)
    } else if (t === TYPE.MAUDITE_OFF) {
      this.grille[y][x] = TYPE.MAUDITE_ON;
      this.mauditeTimers[`${x},${y}`] = this.mauditeDuree;
      this.mauditesVisitees.add(`${x},${y}`);
    } else if (t === TYPE.MAUDITE_ON) {
      // re-allumer = réinitialise la durée
      this.mauditeTimers[`${x},${y}`] = this.mauditeDuree;
      this.mauditesVisitees.add(`${x},${y}`);
    }
  }

  // Glissade sur la glace : continue dans la même direction
  _glisser(dy, dx) {
    const nx = this.joueur.x + dx;
    const ny = this.joueur.y + dy;

    if (!this._dansGrille(nx, ny)) {
      this._notifierMaj();
      this.dessiner();
      return;
    }
    const cible = this.grille[ny][nx];
    if (cible === TYPE.MUR || cible === TYPE.TROU) {
      this._notifierMaj();
      this.dessiner();
      return;
    }

    const oldX = this.joueur.x;
    const oldY = this.joueur.y;
    const oldType = this.grille[oldY][oldX];

    if (oldType === TYPE.FRAGILE) {
      this.grille[oldY][oldX] = TYPE.TROU;
      this.totalAllumables--;
    }

    this._appliquerEntreeCase(nx, ny);

    this.joueur.x = nx;
    this.joueur.y = ny;
    // nbDeplacements et compteurPas non incrémentés : toute la glissade = 1 seul déplacement
    // Le piège ne se déclenche pas en cours de glissade, seulement à l'arrêt

    // Si le joueur atterrit directement sur un piège pendant la glissade → reset
    const surPiege = this.grille[ny][nx] === TYPE.PIEGE;
    const piegeDeclenche = surPiege;

    if (piegeDeclenche) {
      this._lancerAnimation(
        oldX,
        oldY,
        nx,
        ny,
        () => {
          if (this.callbacks.onMessage)
            this.callbacks.onMessage("💥 Piège déclenché !");
          this.recommencer();
        },
        80,
      );
      return;
    }

    // Téléporteur pendant glissade
    if (cible === TYPE.TELEPORT_A || cible === TYPE.TELEPORT_B) {
      const dest =
        cible === TYPE.TELEPORT_A ? this.teleports.B : this.teleports.A;
      if (dest && (dest.x !== nx || dest.y !== ny)) {
        this._lancerAnimation(
          oldX,
          oldY,
          nx,
          ny,
          () => {
            this.joueur.x = dest.x;
            this.joueur.y = dest.y;
            this._appliquerEntreeCase(dest.x, dest.y);
            this._notifierMaj();
            this.dessiner();
            this._verifierVictoire(cible);
          },
          80,
        );
        return;
      }
    }

    if (cible === TYPE.GLACE) {
      // continue à glisser — on notifie le HUD (cases) en cours de route
      this._notifierMaj();
      this._lancerAnimation(
        oldX,
        oldY,
        nx,
        ny,
        () => {
          this._glisser(dy, dx);
        },
        80,
      );
      return;
    }

    // Victoire ?
    if (this._verifierVictoireImmediat(cible, oldX, oldY, nx, ny)) return;

    if (this.callbacks.onDeplacement)
      this.callbacks.onDeplacement(this.nbDeplacements);
    this._notifierMaj();
    this._lancerAnimation(oldX, oldY, nx, ny, null, 80);
  }

  // Parcourt la grille : retourne false si une case éteinte ou maudite éteinte existe
  _toutesLesCasesAllumees() {
    for (let y = 0; y < this.hauteur; y++) {
      for (let x = 0; x < this.largeur; x++) {
        const t = this.grille[y][x];
        if (t === TYPE.ETEINTE)     return false;
        if (t === TYPE.MAUDITE_OFF) return false;
      }
    }
    return true;
  }

  _verifierVictoireImmediat(cible, oldX, oldY, nx, ny) {
    if (cible !== TYPE.SORTIE) return false;
    if (this._toutesLesCasesAllumees()) {
      this.termine = true;
      if (this.callbacks.onDeplacement)
        this.callbacks.onDeplacement(this.nbDeplacements);
      this._notifierMaj();
      this._lancerAnimation(oldX, oldY, nx, ny, () => {
        if (this.callbacks.onVictoire)
          this.callbacks.onVictoire(this.nbDeplacements);
      });
      return true;
    }
    return false;
  }

  _verifierVictoire(cibleType) {
    if (cibleType !== TYPE.SORTIE) return;
    if (this._toutesLesCasesAllumees()) {
      this.termine = true;
      if (this.callbacks.onVictoire)
        this.callbacks.onVictoire(this.nbDeplacements);
    }
  }

  // ===========================================================
  // TICKS : maudites & pièges
  // ===========================================================
  _tickMaudites() {
    for (const key in this.mauditeTimers) {
      this.mauditeTimers[key]--;
      if (this.mauditeTimers[key] <= 0) {
        const [x, y] = key.split(",").map(Number);
        if (this.grille[y][x] === TYPE.MAUDITE_ON) {
          this.grille[y][x] = TYPE.MAUDITE_OFF;
        }
        delete this.mauditeTimers[key];
      }
    }
  }

  // Retourne true si un piège déclenche le reset
  _tickPieges(jx, jy) {
    if (this.compteurPas % this.piegeIntervalle !== 0) return false;
    // Si le joueur est sur un piège -> reset
    if (this.grille[jy][jx] === TYPE.PIEGE) {
      return true;
    }
    return false;
  }

  // ===========================================================
  // UNDO / RESTART
  // ===========================================================
  _sauvegarderEtat() {
    // Snapshot complet (suffisant pour de petites grilles)
    this.historique.push({
      grille: this.grille.map((row) => row.slice()),
      joueur: { ...this.joueur },
      nbDeplacements: this.nbDeplacements,
      compteurPas: this.compteurPas,
      totalAllumables: this.totalAllumables,
      mauditeTimers: { ...this.mauditeTimers },
      mauditesVisitees: new Set(this.mauditesVisitees),
    });
    if (this.historique.length > 200) this.historique.shift();
  }

  annuler() {
    if (this.termine || this.animation) return;
    const snap = this.historique.pop();
    if (!snap) return;
    this.grille = snap.grille;
    this.joueur = snap.joueur;
    this.nbDeplacements = snap.nbDeplacements;
    this.compteurPas = snap.compteurPas;
    this.totalAllumables = snap.totalAllumables;
    this.mauditeTimers = snap.mauditeTimers;
    this.mauditesVisitees = snap.mauditesVisitees;
    this._notifierMaj();
    this.dessiner();
  }

  recommencer() {
    this.historique = [];
    this.termine = false;
    this.animation = null;
    this._initialiser(this.configInitiale);
  }

  // ===========================================================
  // ANIMATION
  // ===========================================================
  _lancerAnimation(fromX, fromY, toX, toY, callback = null, duree = null) {
    this.animation = {
      fromX,
      fromY,
      toX,
      toY,
      debut: performance.now(),
      duree: duree ?? this.dureeAnimation,
      callback,
    };
    this._loopAnim();
  }

  _loopAnim() {
    if (!this.animation) return;
    const now = performance.now();
    const t = Math.min(1, (now - this.animation.debut) / this.animation.duree);
    this.dessiner(t);
    if (t < 1) {
      requestAnimationFrame(() => this._loopAnim());
    } else {
      const cb = this.animation.callback;
      this.animation = null;
      this.dessiner();
      if (cb) cb();
    }
  }

  // ===========================================================
  // NOTIFICATION
  // ===========================================================
  _notifierMaj() {
    if (this.callbacks.onMaj) {
      this.callbacks.onMaj({
        deplacements: this.nbDeplacements,
        allumees: this._compterAllumees(),
        total: this.totalAllumables,
      });
    }
  }

  // ===========================================================
  // DESSIN
  // ===========================================================
  dessiner(progressAnim = 0) {
    const ctx = this.ctx;
    const taille = this.tailleCase;
    ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    for (let y = 0; y < this.hauteur; y++) {
      for (let x = 0; x < this.largeur; x++) {
        const px = x * taille;
        const py = y * taille;
        const t = this.grille[y][x];

        // Fond par défaut : case éteinte
        this._drawImg("eteinte", px, py, taille);

        switch (t) {
          case TYPE.ALLUMEE:
            this._drawImg("allumee", px, py, taille);
            break;
          case TYPE.MUR: {
            const v = this.varianteMurs[`${x},${y}`] || 1;
            this._drawImg("mur" + v, px, py, taille, "#3a3a48");
            break;
          }
          case TYPE.SORTIE:
            this._drawImg("sortie", px, py, taille, "#00aa44", "🚪");
            break;
          case TYPE.GLACE:
            this._drawImg("glace", px, py, taille, "#a8d8ff", "❄");
            break;
          case TYPE.FRAGILE:
            this._drawImg("fragile", px, py, taille, "#c9a87a", "▒");
            break;
          case TYPE.TROU:
            this._drawImg("trou", px, py, taille, "#111", "·");
            break;
          case TYPE.PIEGE: {
            // visuel selon proximité d'activation
            const reste =
              this.piegeIntervalle - (this.compteurPas % this.piegeIntervalle);
            const armé = reste === this.piegeIntervalle; // vient de déclencher = "armé"
            this._drawImg(
              armé ? "piege_on" : "piege_off",
              px,
              py,
              taille,
              armé ? "#aa0000" : "#770000",
              armé ? "✸" : "·",
            );
            break;
          }
          case TYPE.TELEPORT_A:
            this._drawImg("teleport_a", px, py, taille, "#5a00aa", "A");
            break;
          case TYPE.TELEPORT_B:
            this._drawImg("teleport_b", px, py, taille, "#aa00aa", "B");
            break;
          case TYPE.MAUDITE_ON: {
            this._drawImg("maudite_on", px, py, taille, "#aa6600", "★");
            const restant = this.mauditeTimers[`${x},${y}`];
            if (restant !== undefined) {
              ctx.fillStyle = "#fff";
              ctx.strokeStyle = "#000";
              ctx.lineWidth = 3;
              ctx.font = `bold ${taille * 0.4}px Cinzel, serif`;
              ctx.textAlign = "center";
              ctx.textBaseline = "middle";
              const cx = px + taille / 2;
              const cy = py + taille / 2;
              ctx.strokeText(restant, cx, cy);
              ctx.fillText(restant, cx, cy);
            }
            break;
          }
          case TYPE.MAUDITE_OFF:
            this._drawImg("maudite_off", px, py, taille, "#553300", "☆");
            break;
        }
      }
    }

    // Joueur
    const anim = this.animation;
    let rx, ry;
    if (anim) {
      rx = anim.fromX + (anim.toX - anim.fromX) * progressAnim;
      ry = anim.fromY + (anim.toY - anim.fromY) * progressAnim;
    } else {
      rx = this.joueur.x;
      ry = this.joueur.y;
    }

    const skinActif = window.SkinSecret && window.SkinSecret.estActif();
    let nomImg = "joueur_" + this.direction;
    if (skinActif && anim) {
      // Alterne marche
      const frameMarche = Math.floor(progressAnim * 2) % 2 === 0;
      if (frameMarche && this.images[nomImg + "_marche"]) {
        nomImg = nomImg + "_marche";
      }
    }
    this._drawImg(nomImg, rx * taille, ry * taille, taille, null, "☺");
  }

  _drawImg(nom, px, py, taille, fallbackColor = null, fallbackTxt = null) {
    const img = this.images[nom];
    if (img && img.complete && img.naturalWidth > 0) {
      this.ctx.drawImage(img, px, py, taille, taille);
    } else if (fallbackColor) {
      this.ctx.fillStyle = fallbackColor;
      this.ctx.fillRect(px, py, taille, taille);
      if (fallbackTxt) {
        this.ctx.fillStyle = "#fff";
        this.ctx.font = `${taille * 0.5}px serif`;
        this.ctx.textAlign = "center";
        this.ctx.textBaseline = "middle";
        this.ctx.fillText(fallbackTxt, px + taille / 2, py + taille / 2);
      }
    }
  }
}

// Expose globalement
window.TileBurnerCampagne = TileBurnerCampagne;
window.TYPE_CAMPAGNE = TYPE;