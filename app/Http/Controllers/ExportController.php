<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    /**
     * Export a single snippet as JSON
     */
    public function exportSnippetJson(Snippet $snippet)
    {
        // Check authorization
        if ($snippet->user_id !== Auth::id() && !$snippet->is_public) {
            abort(403, 'Unauthorized to export this snippet');
        }

        $data = [
            'title' => $snippet->title,
            'description' => $snippet->description,
            'language' => $snippet->language,
            'code' => $snippet->code,
            'tags' => $snippet->tags->pluck('name')->toArray(),
            'is_public' => $snippet->is_public,
            'created_at' => $snippet->created_at,
            'updated_at' => $snippet->updated_at,
        ];

        $filename = 'snippet_' . $snippet->id . '_' . date('Y-m-d_His') . '.json';
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Export a single snippet as PDF
     */
    public function exportSnippetPdf(Snippet $snippet)
    {
        // Check authorization
        if ($snippet->user_id !== Auth::id() && !$snippet->is_public) {
            abort(403, 'Unauthorized to export this snippet');
        }

        $html = $this->generateSnippetHtml($snippet);
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'snippet_' . $snippet->id . '_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export multiple snippets as JSON (bulk)
     */
    public function exportBulkJson(Request $request)
    {
        $snippetIds = $request->input('ids', []);
        
        if (empty($snippetIds)) {
            return response()->json(['error' => 'No snippets selected'], 400);
        }

        $snippets = Snippet::whereIn('id', $snippetIds)
            ->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhere('user_id', Auth::id());
            })
            ->with('tags')
            ->get();

        $data = $snippets->map(function ($snippet) {
            return [
                'id' => $snippet->id,
                'title' => $snippet->title,
                'description' => $snippet->description,
                'language' => $snippet->language,
                'code' => $snippet->code,
                'tags' => $snippet->tags->pluck('name')->toArray(),
                'is_public' => $snippet->is_public,
                'created_at' => $snippet->created_at,
                'updated_at' => $snippet->updated_at,
            ];
        })->toArray();

        $filename = 'snippets_export_' . date('Y-m-d_His') . '.json';
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Export multiple snippets as PDF (bulk)
     */
    public function exportBulkPdf(Request $request)
    {
        $snippetIds = $request->input('ids', []);
        
        if (empty($snippetIds)) {
            return response()->json(['error' => 'No snippets selected'], 400);
        }

        $snippets = Snippet::whereIn('id', $snippetIds)
            ->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhere('user_id', Auth::id());
            })
            ->with('tags')
            ->get();

        $html = '<h1>Code Snippets Export</h1>';
        $html .= '<p>Exported on: ' . date('Y-m-d H:i:s') . '</p>';
        
        foreach ($snippets as $snippet) {
            $html .= $this->generateSnippetHtml($snippet) . '<div style="page-break-after: always;"></div>';
        }

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'snippets_export_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export all user's snippets as JSON
     */
    public function exportAllJson()
    {
        $snippets = Snippet::where('user_id', Auth::id())
            ->with('tags')
            ->get();

        $data = $snippets->map(function ($snippet) {
            return [
                'id' => $snippet->id,
                'title' => $snippet->title,
                'description' => $snippet->description,
                'language' => $snippet->language,
                'code' => $snippet->code,
                'tags' => $snippet->tags->pluck('name')->toArray(),
                'is_public' => $snippet->is_public,
                'created_at' => $snippet->created_at,
                'updated_at' => $snippet->updated_at,
            ];
        })->toArray();

        $filename = 'all_snippets_' . date('Y-m-d_His') . '.json';
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Export all user's snippets as PDF
     */
    public function exportAllPdf()
    {
        $snippets = Snippet::where('user_id', Auth::id())
            ->with('tags')
            ->get();

        $html = '<h1>My Code Snippets</h1>';
        $html .= '<p>Exported on: ' . date('Y-m-d H:i:s') . '</p>';
        
        foreach ($snippets as $snippet) {
            $html .= $this->generateSnippetHtml($snippet) . '<div style="page-break-after: always;"></div>';
        }

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        $filename = 'all_snippets_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate HTML for a single snippet
     */
    private function generateSnippetHtml(Snippet $snippet)
    {
        $tagsHtml = '';
        if ($snippet->tags->count() > 0) {
            $tagsHtml = '<p><strong>Tags:</strong> ' . implode(', ', $snippet->tags->pluck('name')->toArray()) . '</p>';
        }

        $descriptionHtml = '';
        if (!empty($snippet->description)) {
            $descriptionHtml = '<p><strong>Description:</strong></p><p>' . nl2br(htmlspecialchars($snippet->description)) . '</p>';
        }

        $visibilityBadge = $snippet->is_public ? 'Public' : 'Private';
        $code = htmlspecialchars($snippet->code);

        return <<<HTML
<div style="margin-bottom: 30px; page-break-inside: avoid;">
    <h2>{$snippet->title}</h2>
    <p><strong>Language:</strong> {$snippet->language} | <strong>Visibility:</strong> {$visibilityBadge}</p>
    {$tagsHtml}
    {$descriptionHtml}
    <p><strong>Code:</strong></p>
    <pre style="background-color: #f5f5f5; padding: 12px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 12px;">{$code}</pre>
</div>
HTML;
    }
    }

