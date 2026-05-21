import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard, logout } from '@/routes';

const API_DOWNLOAD_URL = '/download';

function downloadBlob(blob: Blob, filename: string) {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

export default function Dashboard() {
    const [accessKey, setAccessKey] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [status, setStatus] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const handleDownload = async (type: 'pdf' | 'xml') => {
        setError(null);
        setStatus(null);

        if (!/^[0-9]{44}$/.test(accessKey)) {
            setError('Apenas números. Não utilize espaços ou traços.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(`${API_DOWNLOAD_URL}/${type}/${accessKey}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok) {
                const message =
                    payload?.message ||
                    payload?.errors?.[0] ||
                    `Falha ao enfileirar o download ${type.toUpperCase()}: ${response.status} ${response.statusText}`;
                setError(message);
                return;
            }

            setStatus(payload?.message || `Download de ${type.toUpperCase()} enfileirado com sucesso.`);

            const token = payload?.token;

            if (token) {
                let attempts = 0;
                const maxAttempts = 120; // 2 minutes polling

                const interval = window.setInterval(async () => {
                    attempts += 1;

                    try {
                        const r = await fetch(`${API_DOWNLOAD_URL}/result/${token}`, {
                            method: 'GET',
                            credentials: 'same-origin',
                        });

                        if (r.status === 202) {
                            // still processing
                            return;
                        }

                        if (!r.ok) {
                            const p = await r.json().catch(() => null);
                            const message = p?.message || `Falha ao recuperar resultado: ${r.status}`;
                            setError(message);
                            clearInterval(interval);
                            return;
                        }

                        const blob = await r.blob();
                        const contentDisposition = r.headers.get('Content-Disposition') ?? '';
                        const filenameMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
                        const filename = filenameMatch ? filenameMatch[1] : `nota.${type}`;

                        downloadBlob(blob, filename);
                        setStatus(`Download de ${type.toUpperCase()} concluído.`);
                        clearInterval(interval);
                    } catch (e) {
                        // ignore and retry
                    }

                    if (attempts >= maxAttempts) {
                        setError('Tempo esgotado ao aguardar o processamento do download.');
                        clearInterval(interval);
                    }
                }, 1000);
            }
        } catch (exception) {
            setError('Erro ao processar o download. Verifique a chave e tente novamente.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 dark:border-sidebar-border">
                    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-xl font-semibold">Download de NF-e</h2>
                            <p className="text-sm text-muted-foreground">
                                Insira a chave de acesso da nota fiscal com exatamente 44 dígitos. A API do MeuDanfe utiliza a variável de ambiente <code className="rounded bg-muted px-1 py-0.5">MEUDANFE_API_KEY</code>.
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link as="button" href={logout()}>
                                Logout
                            </Link>
                        </Button>
                    </div>

                    <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                        <div>
                            <Label htmlFor="access_key">Chave de acesso (44 dígitos)</Label>
                            <Input
                                id="access_key"
                                name="access_key"
                                value={accessKey}
                                onChange={(event) => setAccessKey(event.target.value.replace(/[^0-9]/g, ''))}
                                placeholder="00000000000000000000000000000000000000000000"
                                maxLength={44}
                                inputMode="numeric"
                                className="mt-1"
                            />
                            <p className="mt-2 text-sm text-muted-foreground">
                                Apenas números. Não use espaços ou traços.
                            </p>
                            {error ? (
                                <p className="mt-2 text-sm text-destructive">{error}</p>
                            ) : status ? (
                                <p className="mt-2 text-sm text-foreground">{status}</p>
                            ) : null}
                        </div>

                        <div className="flex flex-col gap-2">
                            <Button
                                disabled={loading}
                                onClick={() => handleDownload('pdf')}
                            >
                                Baixar PDF
                            </Button>
                            <Button
                                variant="outline"
                                disabled={loading}
                                onClick={() => handleDownload('xml')}
                            >
                                Baixar XML
                            </Button>
                        </div>
                    </div>
                </div>

                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
