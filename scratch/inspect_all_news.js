import { execSync } from 'child_process';

const phpScript = `
\$articles = \\App\\Models\\NewsArticle::orderBy('id', 'desc')->take(25)->get();
\$data = [];
foreach (\$articles as \$a) {
    \$url = \$a->source_url;
    \$isLive = !empty(\$url) && !str_contains(\$url, 'example.com') && !str_contains(\$url, 'gnews.io');
    \$data[] = [
        'title' => \$a->title,
        'source_name' => \$a->source_name ?? '—',
        'source_url' => \$url ?: '(None - Demo Fallback)',
        'status' => \$isLive ? 'Live API (Original Publisher)' : 'Demo Data (Seeder Fallback)',
    ];
}
echo json_encode(\$data, JSON_PRETTY_PRINT);
`;

try {
    const output = execSync(`php artisan tinker --execute="${phpScript.replace(/"/g, '\\"')}"`, { encoding: 'utf-8' });
    console.log(output);
} catch (e) {
    console.error(e.stdout || e.message);
}
