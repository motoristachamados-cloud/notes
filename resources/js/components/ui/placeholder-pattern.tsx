import { useEffect, useId, useState, type ReactNode } from 'react';
import { toDataURL } from 'qrcode';

interface PlaceholderPatternProps {
    className?: string;
    qrValue?: string;
    children?: ReactNode;
}

export function PlaceholderPattern({ className, qrValue, children }: PlaceholderPatternProps) {
    const patternId = useId();
    const [qrDataUrl, setQrDataUrl] = useState<string | null>(null);

    useEffect(() => {
        if (!qrValue) {
            setQrDataUrl(null);
            return;
        }

        let canceled = false;

        toDataURL(qrValue, { margin: 0, width: 256 })
            .then((url: string) => {
                if (!canceled) {
                    setQrDataUrl(url);
                }
            })
            .catch(() => {
                if (!canceled) {
                    setQrDataUrl(null);
                }
            });

        return () => {
            canceled = true;
        };
    }, [qrValue]);

    return (
        <div className={`${className ?? ''}`}>
            <svg className="h-full w-full block" fill="none" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden>
                <defs>
                    <pattern
                        id={patternId}
                        x="0"
                        y="0"
                        width={qrDataUrl ? '1' : '10'}
                        height={qrDataUrl ? '1' : '10'}
                        patternUnits={qrDataUrl ? 'objectBoundingBox' : 'userSpaceOnUse'}
                    >
                        {qrDataUrl ? (
                            <image
                                href={qrDataUrl}
                                x="0"
                                y="0"
                                width="1"
                                height="1"
                                preserveAspectRatio="xMidYMid slice"
                            />
                        ) : (
                            <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                        )}
                    </pattern>
                </defs>

                <rect stroke="none" fill={`url(#${patternId})`} width="100%" height="100%"></rect>
            </svg>

            {children && typeof children === 'object' ? (
                <div className="absolute inset-0 flex h-full w-full pointer-events-none">
                    <div className="pointer-events-auto w-full h-full">
                        {children}
                    </div>
                </div>
            ) : null}
        </div>
    );
}
