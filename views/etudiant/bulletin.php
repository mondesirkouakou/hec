<?php
$isDownload = isset($download) && $download;

if (!$isDownload) {
    $pageTitle = 'Mon bulletin';
    ob_start();
}
?>

<?php if (!$isDownload): ?>
<?php
// Mode bulletin admin : possibilité de choisir semestre et session
$adminBulletinMode = isset($isAdminBulletin) && $isAdminBulletin
    && isset($semestresDisponibles) && is_array($semestresDisponibles) && count($semestresDisponibles) > 0;

// URL et libellé du bouton de retour selon le contexte
if ($adminBulletinMode) {
    $backUrl = BASE_URL . 'admin/dashboard';
    $backLabel = 'Retour au dashboard admin';
} else {
    $backUrl = isset($backUrlStudent) && !empty($backUrlStudent) ? $backUrlStudent : (BASE_URL . 'etudiant/dashboard');
    $backLabel = 'Retour au dashboard';
}
?>
<style>
@media screen {
    .page {
        width: min(210mm, 100%) !important;
        margin: 0 auto !important;
    }

    @media (max-width: 768px) {
        .page {
            width: 100% !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 12px !important;
            box-shadow: none !important;
        }

        .page .header h2 {
            font-size: 16px !important;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-scroll table {
            min-width: 900px;
        }
    }
}

@media print {
    .page {
        width: 210mm !important;
        height: 297mm !important;
        max-height: 297mm !important;
        margin: 0 !important;
        padding: 8mm !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }
}
</style>
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($backLabel) ?>
    </a>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="printBulletin();">
        <i class="fas fa-print"></i> Imprimer mon bulletin
    </button>
    <script>
    function printBulletin() {
        // Détecter si on est sur mobile
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;
        
        if (isMobile) {
            document.body.classList.add('mobile-print');
        }
        
        window.print();
        
        // Retirer la classe après l'impression
        setTimeout(function() {
            document.body.classList.remove('mobile-print');
        }, 1000);
    }
    </script>
</div>
<?php if ($adminBulletinMode): ?>
    <div class="no-print mb-3">
        <form method="get" action="<?= htmlspecialchars($adminBulletinBaseUrl ?? '') ?>" class="d-flex flex-wrap align-items-end" style="gap:8px;">
            <div>
                <label class="form-label" style="font-weight:bold;font-size:12px;">Semestre</label>
                <select name="semestre_id" class="form-select form-select-sm">
                    <?php foreach ($semestresDisponibles as $sem): ?>
                        <option value="<?= (int)$sem['id'] ?>" <?= (isset($semestreId) && (int)$semestreId === (int)$sem['id'] ? 'selected' : '') ?>>
                            Semestre <?= (int)$sem['numero'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-weight:bold;font-size:12px;">Session</label>
                <select name="session" class="form-select form-select-sm">
                    <?php
                    $sessCourante = isset($sessionNumero) ? (int)$sessionNumero : 1;
                    if ($sessCourante < 1 || $sessCourante > 4) { $sessCourante = 1; }
                    for ($s = 1; $s <= 4; $s++): ?>
                        <option value="<?= $s ?>" <?= $sessCourante === $s ? 'selected' : '' ?>>Session <?= $s ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-primary">Afficher</button>
            </div>
        </form>
    </div>
<?php endif; ?>
<?php endif; ?>

<div class="page" style="width:210mm;min-height:297mm;background-color:white;margin:0 auto;padding:12mm;box-shadow:0 0 10px rgba(0,0,0,0.1);box-sizing:border-box;font-size:14px;font-family:Arial,sans-serif;position:relative;padding-bottom:6mm;display:flex;flex-direction:column;">
    <div class="page-main" style="flex:1 0 auto;">
    <div class="header" style="margin-bottom:15px;text-transform:uppercase;font-weight:bold;line-height:1.4;">
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

    <div class="student-info" style="border:1px solid #000;padding:8px;margin-bottom:15px;">
        <div class="info-row" style="display:flex;margin-bottom:5px;">
            <div class="info-col" style="flex:1;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">CLASSE:</span> <?= htmlspecialchars($classe['intitule'] ?? '') ?></div>
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
            <div class="info-col" style="flex:1;margin-left:30px;"><span class="label" style="font-weight:bold;display:inline-block;min-width:80px;">N° MATRICULE:</span> <?= htmlspecialchars($etudiant['matricule'] ?? '') ?></div>
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
    <div class="sub-header" style="text-align:center;font-weight:bold;margin-bottom:10px;">SEMESTRE <?= (int)$semestreNumero ?></div>

    <!-- Tableau des notes dynamique basé sur $notes -->
    <div class="table-scroll">
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:13px;">
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
                        <!-- Coefficients = champ coefficient saisi par l'admin (classe_matiere) -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?= isset($n['coefficient']) ? (float)$n['coefficient'] : 0 ?>
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
                                if (isset($n['note_classe_calculee']) && $n['note_classe_calculee'] !== null && $n['note_classe_calculee'] !== '') {
                                    echo number_format((float)$n['note_classe_calculee'], 2, ',', ' ');
                                } elseif (isset($n['moyenne_classe'])) {
                                    echo number_format((float)$n['moyenne_classe'], 2, ',', ' ');
                                } else {
                                    echo '-';
                                }
                            }
                            ?>
                        </td>
                        <!-- Note d'examen (saisie par l'admin) -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?= isset($n['note_examen']) ? number_format((float)$n['note_examen'], 2, ',', ' ') : '-' ?>
                        </td>
                        <!-- Moyenne finale: 40% note de classe / 60% note d'examen -->
                        <td style="border:1px solid #000;padding:4px;">
                            <?php
                            // On privilégie la moyenne de classe recalculée par le contrôleur si présente
                            if (isset($n['note_classe_calculee']) && $n['note_classe_calculee'] !== null && $n['note_classe_calculee'] !== '') {
                                $noteClasse = (float)$n['note_classe_calculee'];
                            } else {
                                $noteClasse = isset($n['note']) ? (float)$n['note'] : null;
                            }
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
</div>

    <div class="footer-summary" style="margin-top:10px;border:1px solid #000;padding:8px;display:flex;justify-content:space-between;font-weight:bold;text-transform:uppercase;">
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

    <div class="appreciation" style="margin-top:10px;margin-bottom:20px;font-weight:bold;">
        APPRECIATION DU JURY:
        <span style="margin-left:20px;">TRÈS BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">ASSEZ-BIEN <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">PASSABLE <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
        <span style="margin-left:10px;">FAIBLE <span class="checkbox" style="display:inline-block;width:12px;height:12px;border:1px solid #000;margin-right:5px;vertical-align:middle;"></span></span>
    </div>

    <div class="signatures" style="margin-top:20px;text-align:right;">
        <div>Fait à Abidjan, le <?= date('d/m/Y') ?></div>
        <div style="margin-top:10px;font-weight:bold;text-decoration:underline;">LE DIRECTEUR PEDAGOGIQUE DELEGUE</div>
    </div>

    </div><!-- /.page-main -->

    <div class="page-footer" style="margin-top:20mm;border-top:1px solid #000;padding-top:5px;text-align:center;font-size:10px;color:#444;">
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
    size: A4 portrait;
    margin: 0 !important;
}

@media print {
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        width: 210mm !important;
        height: 297mm !important;
        overflow: hidden !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body * { visibility: hidden; }
    .page, .page * { visibility: visible; }
    .no-print { display: none !important; }

    .page {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 210mm !important;
        height: 297mm !important;
        max-width: 210mm !important;
        max-height: 297mm !important;
        margin: 0 !important;
        padding: 8mm !important;
        box-shadow: none !important;
        border: none !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
        transform-origin: top left !important;
    }

    .page-main {
        flex: 1;
        overflow: hidden !important;
    }

    .page table {
        font-size: 11px !important;
    }

    .page th, .page td {
        padding: 3px !important;
    }

    .page .header h2 {
        font-size: 16px !important;
        margin: 8px 0 !important;
    }

    .page .student-info {
        padding: 6px !important;
        margin-bottom: 10px !important;
    }

    .page .footer-summary {
        margin-top: 8px !important;
        padding: 6px !important;
    }

    .page .appreciation {
        margin-top: 8px !important;
        margin-bottom: 15px !important;
        font-size: 12px !important;
    }

    .page .signatures {
        margin-top: 15px !important;
    }

    .page .page-footer {
        margin-top: 10mm !important;
        font-size: 9px !important;
    }
}

/* Styles d'impression spécifiques pour mobile (activés via JavaScript) */
@media print {
    /* Quand mobile-print est actif, on change l'approche */
    body.mobile-print {
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
    }

    body.mobile-print * {
        visibility: visible !important;
    }

    body.mobile-print .no-print,
    body.mobile-print .animated-header,
    body.mobile-print .animated-footer,
    body.mobile-print .navbar,
    body.mobile-print .back-to-top {
        display: none !important;
        visibility: hidden !important;
    }

    body.mobile-print .page {
        position: relative !important;
        width: 100% !important;
        height: auto !important;
        max-width: 100% !important;
        max-height: none !important;
        padding: 5mm !important;
        transform: scale(0.78) !important;
        transform-origin: top left !important;
    }

    body.mobile-print .page table {
        font-size: 9px !important;
    }

    body.mobile-print .page th,
    body.mobile-print .page td {
        padding: 2px !important;
    }
}
</style>
