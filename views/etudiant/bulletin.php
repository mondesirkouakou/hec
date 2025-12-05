<?php
$isDownload = isset($download) && $download;

if (!$isDownload) {
    $pageTitle = 'Mon bulletin';
    ob_start();
}
?>

<?php if (!$isDownload): ?>
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="<?= BASE_URL ?>etudiant/dashboard" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour au dashboard
    </a>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print();">
        <i class="fas fa-print"></i> Imprimer mon bulletin
    </button>
</div>
<?php endif; ?>

<div class="page" style="width:210mm;min-height:297mm;background-color:white;margin:0 auto;padding:15mm;box-shadow:0 0 10px rgba(0,0,0,0.1);box-sizing:border-box;font-size:11px;font-family:Arial,sans-serif;position:relative;padding-bottom:25mm;">
    <div class="header" style="margin-bottom:20px;text-transform:uppercase;font-weight:bold;line-height:1.4;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="text-align:left;">
                MINISTERE DE L'ENSEIGNEMENT SUPERIEUR<br>
                DE LA RECHERCHE SCIENTIFIQUE<br>
                <span style="display:inline-block;margin-top:5px;">
                    <img src="<?= BASE_URL ?>assets/images/logo.png" alt="HEC Abidjan" style="height:40px;">
                </span>
            </div>
            <div style="text-align:right;">DP/DIEET</div>
        </div>
        <div style="text-align:center;margin-top:10px;">
            <h2 style="font-size:18px;margin:10px 0;text-decoration:underline;">RELEVE DE NOTES</h2>
            <div>ANNEE ACADEMIQUE: <?= htmlspecialchars($annee_academique ?? '2024-2025') ?></div>
        </div>
    </div>

    <div class="student-info" style="border:1px solid #000;padding:10px;margin-bottom:20px;">
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">MENTION:</span> <?= htmlspecialchars($classe['intitule'] ?? '') ?></div>
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">PARCOURS:</span> LICENCE</div>
        </div>
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">NOM:</span> <?= htmlspecialchars($etudiant['nom'] ?? '') ?></div>
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">PRENOMS:</span> <?= htmlspecialchars($etudiant['prenom'] ?? '') ?></div>
        </div>
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;">
                <span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">DATE ET LIEU DE NAISSANCE:</span>
                <?php
                $dateNaiss = $etudiant['date_naissance'] ?? null;
                $lieuNaiss = $etudiant['lieu_naissance'] ?? null;
                $dateNaissFormatee = '';
                if (!empty($dateNaiss)) {
                    $ts = strtotime($dateNaiss);
                    if ($ts !== false) {
                        $dateNaissFormatee = date('d/m/Y', $ts);
                    }
                }
                echo htmlspecialchars(trim($dateNaissFormatee . (!empty($lieuNaiss) ? ' à ' . $lieuNaiss : '')));
                ?>
            </div>
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">N° MATRICULE:</span> <?= htmlspecialchars($etudiant['matricule'] ?? '') ?></div>
        </div>
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;">
                <span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">SESSION:</span>
                <?php
                $sessionNumero = isset($session) ? (int)$session : 1;
                if ($sessionNumero < 1 || $sessionNumero > 4) {
                    $sessionNumero = 1;
                }
                echo 'Session ' . $sessionNumero;
                ?>
            </div>
            <div class="info-col" style="flex:1;"></div>
        </div>
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;">
                <span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">NIVEAU:</span>
                <?php
                // On construit "Licence 1 IDA" à partir de :
                // - niveau (ex: "Licence" ou "Licence 1")
                // - intitulé (ex: "IDA 3" => filière "IDA" et éventuellement numéro 3)
                $niveauRaw    = trim($classe['niveau'] ?? '');
                $intituleRaw  = trim($classe['intitule'] ?? '');

                // Extraire la filière (lettres) et un éventuel numéro (chiffres) depuis l'intitulé
                $filiere      = preg_replace('/[^A-Za-z]/', '', $intituleRaw); // "IDA 3" -> "IDA"
                $numeroFromIntitule = preg_replace('/[^0-9]/', '', $intituleRaw); // "IDA 3" -> "3"

                // Si le niveau contient déjà un chiffre (ex: "Licence 1"), on le garde tel quel.
                // Sinon, si on a trouvé un numéro dans l'intitulé, on l'ajoute après le niveau.
                if (preg_match('/\d/', $niveauRaw)) {
                    $niveauBase = $niveauRaw;              // ex: "Licence 1"
                } elseif ($numeroFromIntitule !== '') {
                    $niveauBase = trim($niveauRaw . ' ' . $numeroFromIntitule); // ex: "Licence 3"
                } else {
                    $niveauBase = $niveauRaw;              // ex: "Licence"
                }

                $niveauAffiche = trim($niveauBase . ' ' . $filiere); // ex: "Licence 1 IDA" ou "Licence 3 GL"
                echo htmlspecialchars($niveauAffiche);
                ?>
            </div>
            <div class="info-col" style="flex:1;"></div>
        </div>
    </div>

    <?php
        if (!isset($semestreNumero)) {
            $semestreNumero = isset($notes[0]['semestre_numero']) ? (int)$notes[0]['semestre_numero'] : 1;
        }
    ?>
    <div class="sub-header" style="text-align:center;font-weight:bold;margin-bottom:15px;">SEMESTRE <?= (int)$semestreNumero ?></div>

    <!-- Tableau des notes dynamique basé sur $notes -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:20px;font-size:10px;">
        <thead>
            <tr>
                <th style="border:1px solid #000;padding:4px;">ELEMENT CONSTITUTIF DE L'UNITE D'ENSEIGNEMENT (E.C.U.E)</th>
                <th style="border:1px solid #000;padding:4px;">CREDITS</th>
                <th style="border:1px solid #000;padding:4px;">COEFFICIENTS</th>
                <th style="border:1px solid #000;padding:4px;">MOYENNE DE CLASSE</th>
                <th style="border:1px solid #000;padding:4px;">NOTE D'EXAMEN</th>
                <th style="border:1px solid #000;padding:4px;">MOYENNE</th>
                <th style="border:1px solid #000;padding:4px;">STATUT</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notes)): ?>
                <tr>
                    <td colspan="7" style="border:1px solid #000;padding:4px;text-align:center;">Aucune note enregistrée pour le semestre <?= isset($semestreNumero) ? (int)$semestreNumero : 1 ?>.</td>
                </tr>
            <?php else: ?>
                <?php
                // Initialisation pour les calculs de synthèse du semestre
                $moyenneSemestre1 = 0;
                // Total des crédits cumulés (crédits obtenus) pour le semestre
                $totalCreditsSem1 = 0;
                // Somme des moyennes finales pour calculer la moyenne du semestre
                $sommeMoyennesFinales = 0;
                $nbMoyennesFinales = 0;
                // Somme pondérée par les coefficients et somme des coefficients
                $sommeMoyennesPonderees = 0;
                $sommeCoefficients = 0;
                ?>
                <?php foreach ($notes as $n): ?>
                    <tr>
                        <!-- ECUE = nom de la matière -->
                        <td style="border:1px solid #000;padding:4px;text-align:left;">
                            <?= htmlspecialchars($n['matiere_nom'] ?? '') ?>
                        </td>
                        <!-- Credits = champ credits de la matière -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?= isset($n['credits']) ? (float)$n['credits'] : 0 ?>
                        </td>
                        <!-- Coefficients : en l'absence de colonne spécifique, on réutilise credits -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?= isset($n['credits']) ? (float)$n['credits'] : 0 ?>
                        </td>
                        <!-- Moyenne de classe réelle (session 1 uniquement) -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?php
                            $sessionAffichee = isset($sessionNumero) ? (int)$sessionNumero : 1;
                            if ($sessionAffichee < 1 || $sessionAffichee > 4) {
                                $sessionAffichee = 1;
                            }
                            if ($sessionAffichee > 1) {
                                // Sessions 2, 3, 4 : remplir la case avec un fond hachuré pour montrer qu'elle est rayée
                                echo '<div style="width:100%;height:14px;background:repeating-linear-gradient(-45deg,#000 0,#000 1px,transparent 1px,transparent 3px);"></div>';
                            } else {
                                // Session 1 : on affiche la vraie moyenne de classe
                                echo isset($n['moyenne_classe']) ? number_format((float)$n['moyenne_classe'], 2, ',', ' ') : '-';
                            }
                            ?>
                        </td>
                        <!-- Note d'examen (saisie par l'admin) -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?= isset($n['note_examen']) ? number_format((float)$n['note_examen'], 2, ',', ' ') : '-' ?>
                        </td>
                        <!-- Moyenne finale: 40% note de classe (note) / 60% note d'examen -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?php
                            $noteClasse = isset($n['note']) ? (float)$n['note'] : null;
                            $noteExamen = isset($n['note_examen']) ? (float)$n['note_examen'] : null;
                            $moyenneFinale = null;

                            $sessionAffichee = isset($sessionNumero) ? (int)$sessionNumero : 1;
                            if ($sessionAffichee < 1 || $sessionAffichee > 4) {
                                $sessionAffichee = 1;
                            }

                            if ($sessionAffichee > 1) {
                                if ($noteExamen !== null) {
                                    $moyenneFinale = $noteExamen;
                                }
                            } else {
                                if ($noteClasse !== null && $noteExamen !== null) {
                                    $moyenneFinale = 0.4 * $noteClasse + 0.6 * $noteExamen;
                                } elseif ($noteClasse !== null) {
                                    $moyenneFinale = $noteClasse;
                                } elseif ($noteExamen !== null) {
                                    $moyenneFinale = $noteExamen;
                                }
                            }
                            // Crédits cumulés uniquement si la matière est validée
                            if ($moyenneFinale !== null && $moyenneFinale >= 10 && isset($n['credits'])) {
                                $totalCreditsSem1 += (float)$n['credits'];
                            }
                            // Préparation du calcul de la moyenne de semestre (moyenne des moyennes finales)
                            if ($moyenneFinale !== null) {
                                $sommeMoyennesFinales += $moyenneFinale;
                                $nbMoyennesFinales++;

                                // On utilise les CREDITS comme coefficients effectifs
                                $coefficient = isset($n['credits']) ? (float)$n['credits'] : 1;
                                $sommeMoyennesPonderees += $moyenneFinale * $coefficient;
                                $sommeCoefficients += $coefficient;
                            }
                            echo $moyenneFinale !== null ? number_format($moyenneFinale, 2, ',', ' ') : '-';
                            ?>
                        </td>
                        <!-- Statut -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?php if ($moyenneFinale === null): ?>
                                -
                            <?php elseif ($moyenneFinale >= 10): ?>
                                Validée
                            <?php else: ?>
                                Echoué
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php
                    // Calcul effectif de la moyenne de semestre selon la formule institutionnelle :
                    // MOY_SEM = (moy1*crédit1 + moy2*crédit2 + ... + moyX*créditX) / 30
                    $denominateurSemestre = 30;
                    $moyenneSemestre1 = $denominateurSemestre > 0 ? $sommeMoyennesPonderees / $denominateurSemestre : 0;
                ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-summary" style="margin-top:20px;border:1px solid #000;padding:10px;display:flex;justify-content:space-between;font-weight:bold;text-transform:uppercase;">
        <?php
        $sessionAffichee = isset($sessionNumero) ? (int)$sessionNumero : 1;
        if ($sessionAffichee < 1 || $sessionAffichee > 4) {
            $sessionAffichee = 1;
        }

        if ($sessionAffichee === 1): ?>
            <!-- Session 1 : on garde total des crédits cumulés (toutes sessions jusqu'à 1) + moyenne de semestre -->
            <div>TOTAL DES CREDITS CUMULÉS AU SEMESTRE <?= isset($semestreNumero) ? (int)$semestreNumero : 1 ?>: <?= isset($totalCreditsCumul) ? (int)$totalCreditsCumul : 0 ?></div>
            <div>MOYENNE SEMESTRE <?= isset($semestreNumero) ? (int)$semestreNumero : 1 ?>: <?= isset($moyenneSemestre1) ? number_format($moyenneSemestre1, 2, ',', ' ') : '0,00' ?></div>
        <?php else: ?>
            <!-- Sessions 2 à 4 : TOTAL DES CREDITS (obtenus pendant cette session) et TOTAL DES CREDITS CUMULÉS AU SEMESTRE (toutes sessions jusqu'à la session actuelle) -->
            <div>TOTAL DES CREDITS: <?= isset($totalCreditsSem1) ? (int)$totalCreditsSem1 : 0 ?></div>
            <div>TOTAL DES CREDITS CUMULÉS AU SEMESTRE <?= isset($semestreNumero) ? (int)$semestreNumero : 1 ?>: <?= isset($totalCreditsCumul) ? (int)$totalCreditsCumul : 0 ?></div>
        <?php endif; ?>
    </div>

    <div class="appreciation" style="margin-top:15px;margin-bottom:30px;font-weight:bold;">
        APPRECIATION DU JURY:
        <span style="margin-left:20px;">TRÈS BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">ASSEZ-BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">PASSABLE <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">FAIBLE <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
    </div>

    <div class="signatures" style="position:absolute;right:15mm;bottom:95mm;text-align:center;">
        <div>Fait à Abidjan, le <?= date('d/m/Y') ?></div>
        <div style="margin-top:10px;font-weight:bold;text-decoration:underline;">LE DIRECTEUR PEDAGOGIQUE DELEGUE</div>
    </div>

    <div class="page-footer" style="position:absolute;left:15mm;right:15mm;bottom:60mm;border-top:1px solid #000;padding-top:5px;text-align:center;font-size:9px;color:#444;">
        CC-02 193 40 RC 279162 Compte NSIA 21018102004 Centre impôts COCODY 17 BP 84 Abidjan 17 www.hec.ci / email: infos@hecabidjan.ci<br>
        Abidjan Cocody, route de l'Université boulevard F. Mitterrand face Ecole de Gendarmerie<br>
        Tél: (225) 27-22-48-48-13 / Fax (225) 27-22-48-48-14
    </div>
 </div>

<?php
if (!$isDownload) {
    $content = ob_get_clean();
    require_once __DIR__ . '/../layouts/main.php';
}
?>

<style>
@page {
    size: A4;
    margin: 8mm 10mm; /* haut/bas 8mm, gauche/droite 10mm */
}

@media print {
    body * { visibility: hidden; }
    .page, .page * { visibility: visible; }
    .no-print { display: none !important; }

    .page {
        box-shadow: none !important;
        width: auto !important;
        margin: -5mm auto 0 auto !important;   /* léger décalage vers le haut, tout en restant visible */
        padding-top: 0 !important;  /* enlève les 15mm de padding haut définis pour l’écran */
        padding-left: 5mm !important;
        padding-right: 5mm !important;
    }
}
</style>
