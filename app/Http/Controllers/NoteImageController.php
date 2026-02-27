<?php
namespace App\Http\Controllers;

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

            $path = $file->store('notes/images');

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

    /**
     * Удаление изображения (опционально)
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            // Удаляем файл из хранилища
            if (Storage::disk('public')->exists($request->path)) {
                Storage::disk('public')->delete($request->path);

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
