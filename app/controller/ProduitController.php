<?php
require_once '../config/config.php';
require_once '../model/Produit.php';

class ProduitController {
    private string $uploadDir = '../uploads/produits/';

    private function handleImageUpload(): ?string {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize  = 2 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) {
            throw new Exception("Format d'image non autorisé. (jpg, png, webp, gif)");
        }

        if ($file['size'] > $maxSize) {
            throw new Exception("L'image ne doit pas dépasser 2MB.");
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_', true) . '.' . $ext;
        $dest     = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new Exception("Échec lors de l'enregistrement de l'image.");
        }

        return $filename;
    }

    public function showProduit(Produit $produit): void {
        $produit->show();
    }

    public function listProduits(): mixed {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT p.*, c.nom AS categorie 
                                FROM produit p 
                                JOIN categorie c ON p.categorie_id = c.id');
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addProduit(Produit $produit, ?string $imageFile = null): void {
        $db = config::getConnexion();
        try {
            $imageName = $imageFile ?? null;
            
            $req = $db->prepare('
                INSERT INTO produit VALUES
                (NULL, :categorie_id, :nom, :prix, :stock, :description, :image)
            ');
            $req->execute([
                'categorie_id' => $produit->getCategorieId(),
                'nom'          => $produit->getNom(),
                'prix'         => $produit->getPrix(),
                'stock'        => $produit->getStock(),
                'description'  => $produit->getDescription(),
                'image'        => $imageName
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteProduit(int $id): void {
        $db = config::getConnexion();
        
        try {
            $produit = $this->getProduit($id);
            if (!empty($produit['image'])) {
                $filePath = $this->uploadDir . $produit['image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        } catch (Exception $e) {
            // Continue with deletion even if image deletion fails
        }
        
        try {
            $req = $db->prepare('
                DELETE FROM produit
                WHERE id = :id
            ');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getProduit(int $id): mixed {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                SELECT * FROM produit
                WHERE id = :id
            ');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateProduit(int $id, Produit $produit, ?string $imageFile = null): void {
        $db = config::getConnexion();
        try {
            $existingProduit = $this->getProduit($id);
            $imageName = $imageFile ?? $existingProduit['image'] ?? null;
            
            $req = $db->prepare('
                UPDATE produit SET
                    categorie_id = :categorie_id,
                    nom          = :nom,
                    prix         = :prix,
                    stock        = :stock,
                    description  = :description,
                    image        = :image
                WHERE id = :id
            ');
            $req->execute([
                'id'           => $id,
                'categorie_id' => $produit->getCategorieId(),
                'nom'          => $produit->getNom(),
                'prix'         => $produit->getPrix(),
                'stock'        => $produit->getStock(),
                'description'  => $produit->getDescription(),
                'image'        => $imageName
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>