<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(array $data, User $user): Product
    {
        return DB::transaction(function () use ($data, $user) {
            $imagePath = null;
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $imagePath = $this->storeImage($data['image'], $user->primaryOrganization()->id);
            }

            $product = Product::create([
                'organization_id' => $user->primaryOrganization()->id,
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'sku' => $data['sku'],
                'category_id' => $data['category_id'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'description' => $data['description'] ?? null,
                'image' => $imagePath,
                'metadata' => $data['metadata'] ?? [],
            ]);

            return $product->load(['brand', 'category']);
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $this->storeImage($data['image'], $product->organization_id);
            }

            $product->update($data);

            return $product->load(['brand', 'category']);
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->images()->each(function ($image) {
                Storage::disk('public')->delete($image->url);
            });

            $product->variants()->delete();
            $product->images()->delete();

            return $product->delete();
        });
    }

    public function addImage(Product $product, UploadedFile $file, ?int $order = null): ProductImage
    {
        $url = $this->storeImage($file, $product->organization_id);
        $maxOrder = $product->images()->max('order') ?? 0;

        return ProductImage::create([
            'product_id' => $product->id,
            'url' => $url,
            'order' => $order ?? ($maxOrder + 1),
        ]);
    }

    public function removeImage(ProductImage $image): bool
    {
        if ($image->url && Storage::disk('public')->exists($image->url)) {
            Storage::disk('public')->delete($image->url);
        }

        return $image->delete();
    }

    public function updateStock(Product $product, int $quantity, string $operation = 'set'): Product
    {
        return DB::transaction(function () use ($product, $quantity, $operation) {
            $currentStock = $product->stock;

            $newStock = match($operation) {
                'add' => $currentStock + $quantity,
                'subtract' => max(0, $currentStock - $quantity),
                'set' => $quantity,
                default => $currentStock,
            };

            $product->update(['stock' => $newStock]);

            return $product;
        });
    }

    public function importProductsFromFile(UploadedFile $file, User $user, array $options = []): array
    {
        $extension = $file->getClientOriginalExtension();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            if ($extension === 'csv') {
                $data = $this->parseCsv($file);
            } else {
                $data = $this->parseExcel($file);
            }

            foreach ($data as $index => $row) {
                try {
                    $sku = $row['sku'] ?? null;
                    if (!$sku) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 2) . ": SKU is required";
                        continue;
                    }

                    $existingProduct = Product::where('organization_id', $user->primaryOrganization()->id)
                        ->where('sku', $sku)
                        ->first();

                    if ($existingProduct) {
                        if ($options['update_existing'] ?? false) {
                            $this->updateProductFromRow($existingProduct, $row);
                            $imported++;
                        } else {
                            if ($options['skip_duplicates'] ?? true) {
                                $skipped++;
                            } else {
                                throw new \Exception("Product with SKU {$sku} already exists");
                            }
                        }
                    } else {
                        $this->createProductFromRow($row, $user);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to import products: " . $e->getMessage());
        }
    }

    private function createProductFromRow(array $row, User $user): Product
    {
        return $this->createProduct([
            'name' => $row['name'] ?? '',
            'sku' => $row['sku'],
            'brand_id' => $row['brand_id'] ?? null,
            'category_id' => $row['category_id'] ?? null,
            'price' => $row['price'] ?? 0,
            'stock' => $row['stock'] ?? 0,
            'status' => $row['status'] ?? 'draft',
            'description' => $row['description'] ?? null,
            'metadata' => json_decode($row['metadata'] ?? '{}', true),
        ], $user);
    }

    private function updateProductFromRow(Product $product, array $row): Product
    {
        $updateData = [];
        if (isset($row['name'])) $updateData['name'] = $row['name'];
        if (isset($row['price'])) $updateData['price'] = $row['price'];
        if (isset($row['stock'])) $updateData['stock'] = $row['stock'];
        if (isset($row['status'])) $updateData['status'] = $row['status'];
        if (isset($row['description'])) $updateData['description'] = $row['description'];

        return $this->updateProduct($product, $updateData);
    }

    private function parseCsv(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $data;
    }

    private function parseExcel(UploadedFile $file): array
    {
        // This would require Laravel Excel package
        // For now, return empty array - can be implemented later
        return [];
    }

    private function storeImage(UploadedFile $file, int $organizationId): string
    {
        return $file->store("products/{$organizationId}", 'public');
    }
}

