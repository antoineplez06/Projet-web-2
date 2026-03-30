<?php
$dir = __DIR__ . '/src/Application/Templates';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'twig') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        $replaced = str_replace(
            [
                'extends "base.html.twig"', "extends 'base.html.twig'",
                'extends "base-an.html.twig"', "extends 'base-an.html.twig'",
                'extends "base-admin.html.twig"', "extends 'base-admin.html.twig'",
                'extends "base-pilote.html.twig"', "extends 'base-pilote.html.twig'",
                'include "entreprise_formulaire.twig"', "include 'entreprise_formulaire.twig'",
                'extends "layouts/layouts/' // in case of double replacement
            ],
            [
                'extends "layouts/base.html.twig"', "extends 'layouts/base.html.twig'",
                'extends "layouts/base_anonyme.html.twig"', "extends 'layouts/base_anonyme.html.twig'",
                'extends "layouts/base_admin.html.twig"', "extends 'layouts/base_admin.html.twig'",
                'extends "layouts/base_pilote.html.twig"', "extends 'layouts/base_pilote.html.twig'",
                'include "entreprise/_formulaire.html.twig"', "include 'entreprise/_formulaire.html.twig'",
                'extends "layouts/'
            ],
            $content
        );

        if ($content !== $replaced) {
            file_put_contents($path, $replaced);
            echo "Replaced in " . $path . "\n";
        }
    }
}
