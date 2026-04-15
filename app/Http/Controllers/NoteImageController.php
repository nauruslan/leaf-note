<?php
namespace App\Http\Controllers;

use App\Services\TemporaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoteImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        try {
            $file = $request->file('image');
            $filename = Str::uuid() . '_' . time() . '_' . $file->getClientOriginalName();

            $path = $file->store('notes/images', 'public');

            // Сохраняем путь к временному изображению
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->add($path);

            return response()->json([
                'success' => true,
                'url' => Storage::url($path),
                'path' => $path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка загрузки: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string|max:500'
        ]);

        try {
            $path = str_replace('..', '', $request->path);

            if (!str_starts_with($path, 'notes/')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Неверный путь к файлу'
                ], 403);
            }

            // Удаляем из списка временных изображений
            $temporaryImageService = app(TemporaryImageService::class);
            $temporaryImageService->remove($path);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'success' => true,
                    'message' => 'Изображение удалено'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Файл не найден'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка удаления: ' . $e->getMessage()
            ], 500);
        }
    }
}