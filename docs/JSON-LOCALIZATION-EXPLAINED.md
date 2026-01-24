<!--
SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->

# 🌍 Comment WordPress charge les traductions JavaScript avec les fichiers JSON

## Le Problème

Vous avez vu que wp-cli génère **plusieurs fichiers JSON** avec des noms comme :
```
php-and-js-fr_FR-03273cb8448a05312170701e034b100b.json
php-and-js-fr_FR-345980b30b12cfe078f9cd7c2ae9feac.json
php-and-js-fr_FR-616cd17457f52bc81f2b5fc7db1b0b10.json
...
```

## La Solution : Hash MD5 du chemin du fichier source

WordPress utilise un système de **correspondance par hash MD5** :

### 1. Génération des fichiers JSON (wp-cli)

wp-cli crée **un fichier JSON par fichier source JavaScript** :

| Fichier source | Hash MD5 | Fichier JSON généré |
|----------------|----------|---------------------|
| `admin/script.js` | `345980b30b12cfe078f9cd7c2ae9feac` | `domaine-fr_FR-345980b30b12cfe078f9cd7c2ae9feac.json` |
| `assets/main.js` | `616cd17457f52bc81f2b5fc7db1b0b10` | `domaine-fr_FR-616cd17457f52bc81f2b5fc7db1b0b10.json` |
| `admin/app.js` | `bd531acd6b29cc840c177260c5f68ec2` | `domaine-fr_FR-bd531acd6b29cc840c177260c5f68ec2.json` |

Le hash est calculé : `md5( chemin_relatif_du_fichier_source )`

### 2. Chargement dans WordPress (runtime)

Quand vous enregistrez un script JS avec `wp_enqueue_script()` :

```php
wp_enqueue_script( 'mon-script', 
    plugins_url( 'assets/main.js', __FILE__ ), 
    array( 'wp-i18n' ) 
);

wp_set_script_translations( 'mon-script', 'mon-domaine' );
```

WordPress fait automatiquement :

1. **Calcule le chemin relatif** du fichier JS : `assets/main.js`
2. **Calcule le hash MD5** : `md5('assets/main.js')` = `616cd17457f52bc81f2b5fc7db1b0b10`
3. **Cherche le fichier JSON** correspondant : `mon-domaine-fr_FR-616cd17457f52bc81f2b5fc7db1b0b10.json`
4. **Charge les traductions** et les injecte dans `wp.i18n`

### 3. Utilisation dans JavaScript

Dans votre fichier `assets/main.js` :

```javascript
const { __ } = wp.i18n;

console.log( __( 'Main JavaScript String', 'mon-domaine' ) );
// Affiche: "Chaîne JavaScript principale" (si locale fr_FR)
```

## Exemple Concret

### Fichier PHP (enregistrement)

```php
// i18n-404-tools.php
wp_enqueue_script(
    'i18n-404-tools-modal',
    plugins_url( 'admin/js/i18n-404-tools-modal.js', __FILE__ ),
    array( 'wp-i18n' ),
    '1.0',
    true
);

// Indique à WordPress où trouver les traductions
wp_set_script_translations( 
    'i18n-404-tools-modal',  // Handle du script
    'i18n-404-tools',        // Domaine de traduction
    plugin_dir_path( __FILE__ ) . 'languages'  // Dossier des traductions
);
```

### Fichier JSON généré

Contenu de `i18n-404-tools-fr_FR-abc123def456.json` :

```json
{
    "translation-revision-date": "2026-01-22 10:00+0000",
    "generator": "WP-CLI/2.12.0",
    "source": "admin/js/i18n-404-tools-modal.js",
    "domain": "messages",
    "locale_data": {
        "messages": {
            "": {
                "domain": "messages",
                "lang": "fr_FR",
                "plural-forms": "nplurals=2; plural=(n > 1);"
            },
            "Loading...": ["Chargement..."],
            "Close": ["Fermer"]
        }
    }
}
```

### Utilisation JavaScript

```javascript
// admin/js/i18n-404-tools-modal.js
const { __ } = wp.i18n;

console.log( __( 'Loading...', 'i18n-404-tools' ) );
// Affiche: "Chargement..." (en français)

console.log( __( 'Close', 'i18n-404-tools' ) );
// Affiche: "Fermer"
```

## Pourquoi ce système ?

### Avantages

1. **Performance** : Chaque fichier JS ne charge que ses propres traductions (pas tout le catalogue)
2. **Cache** : Les fichiers JSON peuvent être mis en cache séparément
3. **Modularité** : Chaque composant JS a ses traductions isolées

### Le fichier `source` dans le JSON

Notez la propriété `"source"` dans le JSON :

```json
"source": "admin/js/i18n-404-tools-modal.js"
```

C'est une **métadonnée informative** pour les développeurs. Le vrai mécanisme de correspondance est le **hash MD5 dans le nom du fichier**.

## Vérification Manuelle

Pour vérifier le hash d'un fichier :

```bash
# Le chemin relatif (depuis la racine du plugin)
echo -n "admin/js/i18n-404-tools-modal.js" | md5sum

# Devrait correspondre au hash dans le nom du fichier JSON
```

## Résumé

```
Fichier JS → Chemin relatif → MD5 → Nom du fichier JSON → Correspondance automatique
     ↓                                                              ↓
   Script enregistré avec wp_enqueue_script()              Traductions chargées
   + wp_set_script_translations()                          et injectées dans wp.i18n
```

WordPress gère tout automatiquement ! Vous n'avez qu'à :
1. Générer les fichiers JSON avec wp-cli (ou ce plugin)
2. Appeler `wp_set_script_translations()` avec le bon domaine
3. Utiliser `wp.i18n.__()` dans votre JavaScript

✨ **Les traductions sont automatiquement chargées pour chaque script !**
