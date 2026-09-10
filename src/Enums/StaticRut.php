<?php

namespace Laragear\Rut\Enums;

use Laragear\Rut\Rut;

enum StaticRut: string
{
    /**
     * "Empresa Ficticia Ltda".
     *
     * Standard generic corporate profile (Empresa Ficticia Ltda.) used in SII XML schema examples to validate B2B Invoice (Factura Electrónica Tipo 33) structures.
     * Also, alternative corporate receiver specified in SII integration test suites for credit note (Tipo 61) and debit note (Tipo 56) validations.
     */
    case TestCorporateReceiver = '77.777.777-7';
    case TestCorporateReceiverSecondary = '88.888.888-8';

    /**
     * "Consumidor Final".
     *
     * Standard tax identifier required by SII when issuing electronic sales slips or invoices to anonymous consumers or unregistered local buyers.
     */
    case GenericConsumer = '66.666.666-6';

    /**
     * "Receptor extranjero".
     *
     * Mandatory receiver RUT used in electronic invoicing (DTE) and export/import declarations when selling to non-resident foreign individuals or corporations without a Chilean RUT.
     */
    case ForeignEntity = '55.555.555-5';

    /**
     * "Inversion extranjera" | "RUN de visado".
     *
     * Used in tax returns (such as "Declaraciones Juradas") and payroll system reporting to attribute income or transactions to foreign entities lacking a Chilean ID.
     * Also used as RUN for foreign workers undergoing visa processing.
     */
    case ForeignInvestor = '44.444.444-4';

    /**
     * "Plataforma Digital".
     *
     * Disposed by the SII for digital creators and influencers issuing tax documents for income received from anonymous platform subscribers (Usuarios de Plataformas Digitales).
     */
    case DigitalContentMonetization = '44.444.447-9';

    /**
     * "Servicio de Impuestos Internos".
     *
     * Official RUT of the Chilean Internal Revenue Service (SII). This is the main tax authority that assigns every RUT in the country and receives all tax documents.
     */
    case TaxAuthority = '60.803.000-K';

    /**
     * "Tesorería General de la República".
     *
     * Official RUT of the Chilean General Treasury. This is the government office that collects taxes, manages public money, and pays state obligations.
     */
    case NationalTreasury = '60.805.000-0';

    /**
     * "Subsecretaría de Hacienda".
     *
     * Official RUT of the Undersecretariat of Finance. This is the government body that designs tax rules and coordinates the whole public finance system.
     */
    case FinanceUndersecretariat = '60.801.000-9';

    /**
     * "Servicio de Registro Civil e Identificación".
     *
     * Official RUT of the Civil Registry Service. This is the institution that gives every Chilean their national ID number (RUN), which is the same as their RUT.
     */
    case CivilRegistry = '61.002.000-3';

    /**
     * "Dirección de Presupuestos".
     *
     * Official RUT of the Budget Directorate (DIPRES). This office prepares and controls the national budget of Chile.
     */
    case BudgetOffice = '60.802.000-4';

    /**
     * "Servicio Nacional de Aduanas".
     *
     * Official RUT of the National Customs Service. This is the government agency that controls everything that enters or leaves the country.
     */
    case CustomsService = '60.804.000-5';

    /**
     * Returns the enum as a Rut instance.
     */
    public function toRut(): Rut
    {
        return Rut::parse($this->value);
    }
}
