<?php
    /**
     * @file   modules/point/lang/fr.lang.php
     * @author NHN (developers@xpressengine.com) Traduit par Pierre Duvent <PierreDuvent@gmail.com>
     * @brief  Paquet du langage en français pour le module de Point
     **/

    $lang->point = "Point"; 
    $lang->level = "Niveau"; 

    $lang->about_point_module = "Vous pouvez donnez des poins sur l'action d'écrire/d'ajouter commentaire/de télécharger vers le serveur/de télécharger vers le PC etc.";
    $lang->about_act_config = "Chaque module comme celui de panneau ou de blogue a les actions propres comme \"écrire/supprimer/ajouter un commentaire/supprimer un commentaire\".<br />Vous pouvez ajouter seulement les valeurs des actions pour appliquer le Système de Point au module excepté celui de panneau ou de blogue.<br />Vous pouvez délimiter chaque valeur avec virgule(,)."; 

    $lang->max_level = 'Niveau le plus haut';
    $lang->about_max_level = 'Vous pouvez configurer le niveau le plus haut. Les icônes des niveaux doit être réflechissés et le niveau de 1 000 est la valeur maximum que vous pouvez configurer.'; 

    $lang->level_icon = 'Icône de Niveau';
    $lang->about_level_icon = 'Le Chemin d\'icône est "./module/point/icons/[niveau].gif" et le niveau le plus haut peut différer de l\'ensemble des icônes. Alors faites attention, S.V.P.'; 

    $lang->point_name = 'Nom de Point';
    $lang->about_point_name = 'Vous pouvez nommer le point ou configurer l\'unité du point'; 

    $lang->level_point = 'Point de niveau';
    $lang->about_level_point = 'Le Niveau sera ajusté quand le point devient les valeurs aux Points de Niveaux ci-dessous.'; 

    $lang->disable_download = 'Interdire de télécharger';
    $lang->about_disable_download = "Il est impossible de télécharger quand il n'y a pas de points suffisants. (Sauf les fichier d'images)"; 
    $lang->disable_read_document = '글 열람 금지';
    $lang->about_disable_read_document = '포인트가 없을 경우 글 열람을 금지하게 됩니다';

    $lang->level_point_calc = 'Calcul des Points par Niveau';
    $lang->expression = 'Entrez la formule en Javascript en utilisant la Variable de Niveau <b>i</b>. ex) Math.pow(i, 2) * 90';
    $lang->cmd_exp_calc = 'Calculer';
    $lang->cmd_exp_reset = 'Restaurer';

    $lang->cmd_point_recal = 'Restaurer le Point';
	$lang->about_cmd_point_recal = 'Tous les points seront recalculés basé seulement sur les points des articles/commentaires/annexes/inscription.<br />Après la restauration, Les membres gagneront le point d\'inscription seulement quand il fait de l\'activité dans le site Web.<br />Utilisez cette fonction seulement quand l\'initialisation complète est necessaire comme le cas de transfert des données etc.';

    $lang->point_link_group = 'Changement du Groupe lié à celui du Niveau';
    $lang->point_group_reset_and_add = '설정된 그룹 초기화 후 새 그룹 부여';
    $lang->point_group_add_only = '새 그룹만 부여';
    $lang->about_point_link_group = 'Si vous designez un niveau à un groupe particulier, les utilisateur s sont assignés dans le groupe quand ils s\'avancent au groupe en gagnant des points.';

    $lang->about_module_point = "Vous pouvez configurer les points pour chaque module. Le module qui n'a pas de valeurs utilisera les points par défaut.<br />Tous les points seront restaurés quand on fait de l'action inverse.";

    $lang->point_signup = 'Inscription';
    $lang->point_insert_document = 'Écrire';
    $lang->point_delete_document = 'Supprimer';
    $lang->point_insert_comment = 'Ajouter un Commentaire';
    $lang->point_delete_comment = 'Supprimer un Commentaire';
    $lang->point_upload_file = 'Télécharger les Fichiers sur Serveur';
    $lang->point_delete_file = 'Supprimer un Fichier';
    $lang->point_download_file = 'Télécharger les Fichiers sur PC(Sauf des images)';
    $lang->point_read_document = 'lire';
    $lang->point_voted = 'Être Recommandé';
    $lang->point_blamed = 'Être Blâmé';


    $lang->cmd_point_config = 'Configuration primaire';
    $lang->cmd_point_module_config = 'Configuration du Module';
    $lang->cmd_point_act_config = 'Configuration des Actions de chaque fonction';
    $lang->cmd_point_member_list = 'Liste des Points des Membres';

    $lang->msg_cannot_download = "Vous n'avez pas assez de point pour télécharger";
    $lang->msg_disallow_by_point = "포인트가 부족하여 글을 읽을 수 없습니다 (필요포인트 : %d, 현재포인트 : %d)";

    $lang->point_recal_message = 'En train d\'Adjuster le Point. (%d / %d)';
    $lang->point_recal_finished = 'Recalcul des Points est fini.';
?>
