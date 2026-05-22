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

    const [creditAmount, setCreditAmount] = useState('50');

    const [qrValue, setQrValue] = useState<string | null>(null);
    const [qrError, setQrError] = useState<string | null>(null);
    const [qrStatus, setQrStatus] = useState<string | null>(null);

    const [creditLoading, setCreditLoading] = useState(false);

    const handleGenerateCreditQr = async () => {
        const amount = Number(creditAmount);

        if (!Number.isInteger(amount) || amount < 50) {
            setQrError(
                'Informe um valor inteiro maior ou igual a 50.',
            );

            setQrValue(null);
            setQrStatus(null);

            return;
        }

        setQrError(null);

        setQrStatus(
            'Gerando QR code do Mercado Pago...',
        );

        setCreditLoading(true);

        try {
            const response = await fetch(
                '/api/payments/create',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },

                    credentials: 'include',

                    body: JSON.stringify({
                        credits: amount,
                    }),
                },
            );

            const payload = await response
                .json()
                .catch(() => null);

            if (
                !response.ok
                || payload?.success !== true
            ) {
                const message =
                    payload?.message
                    || `Falha ao criar pagamento: ${response.status}`;

                setQrError(message);

                setQrValue(null);
                setQrStatus(null);

                return;
            }

            const checkoutUrl =
                payload?.data?.checkout_url;

            if (
                !checkoutUrl
                || typeof checkoutUrl !== 'string'
            ) {
                setQrError(
                    'Checkout do Mercado Pago não retornou URL válida.',
                );

                setQrValue(null);
                setQrStatus(null);

                return;
            }

            setQrValue(checkoutUrl);

            setQrStatus(
                'QR code PIX gerado com sucesso.',
            );

        } catch (exception) {

            setQrError(
                'Erro ao criar pagamento. Tente novamente mais tarde.',
            );

            setQrValue(null);
            setQrStatus(null);

        } finally {

            setCreditLoading(false);
        }
    };

    const handleDownload = async (
        type: 'pdf' | 'xml',
    ) => {

        setError(null);
        setStatus(null);

        if (!/^[0-9]{44}$/.test(accessKey)) {
            setError(
                'Apenas números. Não utilize espaços ou traços.',
            );

            return;
        }

        setLoading(true);

        try {
            const response = await fetch(
                `${API_DOWNLOAD_URL}/${type}/${accessKey}`,
                {
                    method: 'GET',

                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },

                    credentials: 'include',
                },
            );

            const payload = await response
                .json()
                .catch(() => null);

            if (!response.ok) {

                const message =
                    payload?.message
                    || payload?.errors?.[0]
                    || `Falha ao enfileirar o download ${type.toUpperCase()}: ${response.status}`;

                setError(message);

                return;
            }

            setStatus(
                payload?.message
                || `Download de ${type.toUpperCase()} enfileirado com sucesso.`,
            );

            const token = payload?.token;

            if (token) {

                let attempts = 0;

                const maxAttempts = 120;

                const interval = window.setInterval(
                    async () => {

                        attempts += 1;

                        try {

                            const r = await fetch(
                                `${API_DOWNLOAD_URL}/result/${token}`,
                                {
                                    method: 'GET',
                                    credentials: 'include',
                                },
                            );

                            if (r.status === 202) {
                                return;
                            }

                            if (!r.ok) {

                                const p = await r
                                    .json()
                                    .catch(() => null);

                                const message =
                                    p?.message
                                    || `Falha ao recuperar resultado: ${r.status}`;

                                setError(message);

                                clearInterval(interval);

                                return;
                            }

                            const blob = await r.blob();

                            const contentDisposition =
                                r.headers.get(
                                    'Content-Disposition',
                                ) ?? '';

                            const filenameMatch =
                                contentDisposition.match(
                                    /filename="?([^";]+)"?/i,
                                );

                            const filename =
                                filenameMatch
                                    ? filenameMatch[1]
                                    : `nota.${type}`;

                            downloadBlob(blob, filename);

                            setStatus(
                                `Download de ${type.toUpperCase()} concluído.`,
                            );

                            clearInterval(interval);

                        } catch (e) {
                            //
                        }

                        if (attempts >= maxAttempts) {

                            setError(
                                'Tempo esgotado ao aguardar o processamento do download.',
                            );

                            clearInterval(interval);
                        }
                    },
                    1000,
                );
            }

        } catch (exception) {

            setError(
                'Erro ao processar o download. Verifique a chave e tente novamente.',
            );

        } finally {

            setLoading(false);
        }
    };

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">

                    {/* FORMULÁRIO */}

                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">

                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20">

                            <div className="relative z-10 flex h-full flex-col items-center justify-center gap-4 bg-background/60 p-4">

                                <div className="w-full text-center">

                                    <div className="text-lg font-semibold leading-tight">
                                        Comprar créditos
                                    </div>

                                    <p className="text-base text-muted-foreground">
                                        Mínimo: 50
                                    </p>
                                </div>

                                <div className="flex items-center gap-2">

                                    <Input
                                        id="credit_amount_small"
                                        name="credit_amount_small"
                                        type="number"
                                        min={50}
                                        step={1}
                                        value={creditAmount}
                                        onChange={(event) =>
                                            setCreditAmount(
                                                event.target.value.replace(
                                                    /[^0-9]/g,
                                                    '',
                                                ),
                                            )
                                        }
                                        className="mt-0 h-10 w-32 text-base"
                                    />

                                    <Button
                                        disabled={creditLoading}
                                        onClick={handleGenerateCreditQr}
                                        size="sm"
                                    >
                                        Gerar
                                    </Button>
                                </div>
                            </div>
                        </PlaceholderPattern>
                    </div>

                    {/* QR CODE */}

                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">

                        <PlaceholderPattern
                            className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20"
                            qrValue={qrValue ?? undefined}
                        >

                            <div className="relative z-10 flex h-full flex-col items-center justify-center gap-4 bg-background/70 p-4">

                                <div className="text-center">

                                    <div className="text-lg font-semibold">
                                        QR Code PIX
                                    </div>

                                    <p className="text-sm text-muted-foreground">
                                        Escaneie para concluir o pagamento
                                    </p>
                                </div>

                                {qrValue ? (
                                    <a
                                        href={qrValue}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="text-sm underline"
                                    >
                                        Abrir checkout Mercado Pago
                                    </a>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        Gere um pagamento no card ao lado
                                    </p>
                                )}

                                {qrStatus ? (
                                    <p className="text-center text-xs text-muted-foreground">
                                        {qrStatus}
                                    </p>
                                ) : null}

                                {qrError ? (
                                    <p className="text-center text-xs text-destructive">
                                        {qrError}
                                    </p>
                                ) : null}
                            </div>
                        </PlaceholderPattern>
                    </div>

                    {/* CARTEIRA */}

                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">

                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20">

                            <div className="relative z-10 flex h-full flex-col justify-center gap-3 bg-background/60 p-6">

                                <div>
                                    <div className="text-sm text-muted-foreground">
                                        Créditos disponíveis
                                    </div>

                                    <div className="text-3xl font-bold">
                                        0
                                    </div>
                                </div>

                                <div className="flex justify-between text-sm">

                                    <span className="text-muted-foreground">
                                        Total gasto
                                    </span>

                                    <span>
                                        0
                                    </span>
                                </div>

                                <div className="flex justify-between text-sm">

                                    <span className="text-muted-foreground">
                                        Pagamentos pendentes
                                    </span>

                                    <span>
                                        0
                                    </span>
                                </div>
                            </div>
                        </PlaceholderPattern>
                    </div>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 dark:border-sidebar-border">

                    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <h2 className="text-xl font-semibold">
                                Download de NF-e
                            </h2>

                            <p className="text-sm text-muted-foreground">
                                Insira a chave de acesso da nota fiscal com exatamente 44 dígitos.
                            </p>
                        </div>

                        <Button variant="outline" asChild>

                            <Link
                                as="button"
                                href={logout()}
                            >
                                Logout
                            </Link>
                        </Button>
                    </div>

                    <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">

                        <div>

                            <Label htmlFor="access_key">
                                Chave de acesso (44 dígitos)
                            </Label>

                            <Input
                                id="access_key"
                                name="access_key"
                                value={accessKey}
                                onChange={(event) =>
                                    setAccessKey(
                                        event.target.value.replace(
                                            /[^0-9]/g,
                                            '',
                                        ),
                                    )
                                }
                                placeholder="00000000000000000000000000000000000000000000"
                                maxLength={44}
                                inputMode="numeric"
                                className="mt-1"
                            />

                            <p className="mt-2 text-sm text-muted-foreground">
                                Apenas números. Não use espaços ou traços.
                            </p>

                            {error ? (
                                <p className="mt-2 text-sm text-destructive">
                                    {error}
                                </p>
                            ) : status ? (
                                <p className="mt-2 text-sm text-foreground">
                                    {status}
                                </p>
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