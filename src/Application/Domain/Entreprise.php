<?php

namespace App\Application\Domain;

use Doctrine\Common\Collections\ArrayCollection; // AJOUTÉ
use Doctrine\Common\Collections\Collection;      // AJOUTÉ
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;             // AJOUTÉ
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'entreprises')]
class Entreprise
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $nom;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $adresse;

    #[Column(type: 'string', length: 14, nullable: false)]
    private string $siret;

    #[Column(type: 'string', length: 100, nullable: false)]
    private string $domaine;

    #[Column(type: 'string', length: 100, nullable: false)]
    private string $taille;

    // --- RELATION : Une entreprise a plusieurs offres ---
    // mappedBy doit correspondre au nom de la propriété "entreprise" dans ton entité Offre
    #[OneToMany(mappedBy: 'entreprise', targetEntity: Offre::class)]
    private Collection $offres;

    public function __construct(
        string $nom,
        string $adresse,
        string $siret,
        string $domaine,
        string $taille,
    ) {
        $this->nom = $nom;
        $this->adresse = $adresse;
        $this->siret = $siret;
        $this->domaine = $domaine;
        $this->taille = $taille;
        $this->offres = new ArrayCollection(); // INITIALISATION IMPORTANTE
    }

    // --- Getters ---

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getSiret(): string { return $this->siret; }
    public function getDomaine(): string { return $this->domaine; }
    public function getTaille(): string { return $this->taille; }

    /**
     * C'est cette méthode que Twig va appeler avec ent.offres
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    // --- Setters ---
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function setAdresse(string $adresse): self { $this->adresse = $adresse; return $this; }
    public function setSiret(string $siret): self { $this->siret = $siret; return $this; }
    public function setDomaine(string $domaine): self { $this->domaine = $domaine; return $this; }
    public function setTaille(string $taille): self { $this->taille = $taille; return $this; }
}