<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BatiStack : Synthèse Hebdomadaire</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 20px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <!-- Header -->
                <tr>
                    <td style="background-color: #1e293b; padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">BatiStack</h1>
                        <p style="color: #94a3b8; margin: 5px 0 0 0;">Synthèse Hebdomadaire des Dépenses</p>
                    </td>
                </tr>

                <!-- Introduction -->
                <tr>
                    <td style="padding: 30px;">
                        <h2 style="color: #1e293b; margin-top: 0;">Récapitulatif de la période</h2>
                        <p style="color: #475569; font-size: 16px;">
                            Du <strong>{{ $start->format('d/m/Y') }}</strong> au <strong>{{ $end->format('d/m/Y') }}</strong>.
                        </p>

                        <!-- KPI Grid -->
                        <table width="100%" cellpadding="0" cellspacing="10">
                            <tr>
                                <td width="50%" style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
                                    <span style="display: block; color: #64748b; font-size: 12px; text-transform: uppercase;">Total Engagé</span>
                                    <span style="display: block; color: #0f172a; font-size: 20px; font-weight: bold; margin-top: 5px;">
                                        {{ Number::currency($stats['total_amount'], 'EUR') }}
                                    </span>
                                </td>
                                <td width="50%" style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
                                    <span style="display: block; color: #64748b; font-size: 12px; text-transform: uppercase;">Nb. de Dépenses</span>
                                    <span style="display: block; color: #0f172a; font-size: 20px; font-weight: bold; margin-top: 5px;">
                                        {{ $stats['count'] }}
                                    </span>
                                </td>
                            </tr>
                            @if($stats['pending_count'] > 0)
                                <tr>
                                    <td colspan="2" style="background-color: #fffbeb; padding: 15px; border-radius: 8px; border: 1px solid #fef3c7; text-align: center;">
                                    <span style="color: #92400e; font-size: 14px;">
                                        ⚠️ <strong>{{ $stats['pending_count'] }}</strong> dépenses sont toujours en attente de validation.
                                    </span>
                                    </td>
                                </tr>
                            @endif
                        </table>

                        <!-- Breakdown by Site -->
                        <h3 style="color: #1e293b; margin-top: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Dépenses par Chantier</h3>
                        <table width="100%" cellpadding="5" cellspacing="0">
                            @foreach($stats['by_site'] as $site => $amount)
                                <tr>
                                    <td style="color: #475569; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $site ?? 'Non spécifié' }}</td>
                                    <td align="right" style="color: #0f172a; font-weight: bold; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                                        {{ Number::currency($amount, 'EUR') }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        <!-- Breakdown by Category -->
                        <h3 style="color: #1e293b; margin-top: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Dépenses par Catégorie</h3>
                        <table width="100%" cellpadding="5" cellspacing="0">
                            @foreach($stats['by_category'] as $category => $amount)
                                <tr>
                                    <td style="color: #475569; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $category }}</td>
                                    <td align="right" style="color: #0f172a; font-weight: bold; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                                        {{ Number::currency($amount, 'EUR') }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        <!-- Action -->
                        <div style="margin-top: 40px; text-align: center;">
                            <a href="{{ config('app.url') }}/admin/expenses" style="background-color: #3b82f6; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                Accéder au Panel d'Administration
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                        <p style="color: #94a3b8; font-size: 12px; margin: 0;">
                            Cet email a été généré automatiquement par le système BatiStack.<br>
                            &copy; {{ date('Y') }} BatiStack - Gestion de Chantier.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


</body>
</html>
