<?php
#Common Expression Tables
trait cets {
    protected $sqlsrv_CU = '';
    protected $id = 0;
    protected $startDate = '';
    protected $endDate = '';
    protected $feStringFilter = '';
    #region cets

    private function cetPaxCount(){ 
            return "PAX_COUNT AS (
                SELECT
                    IIF (
                        FORMAT(TRAVELDATE, 'yyyyMM') >= FORMAT(CAST('{$this->startDate}' AS DATE), 'yyyyMM'),
                        PAX,
                        0
                    ) AS PaxCount,
                    IIF (
                        FORMAT(TRAVELDATE, 'yyyyMM') >= FORMAT(CAST('{$this->startDate}' AS DATE), 'yyyyMM'),
                        ADULTS,
                        0
                    ) AS Adult_Count,
                    IIF (
                        FORMAT(TRAVELDATE, 'yyyyMM') >= FORMAT(CAST('{$this->startDate}' AS DATE), 'yyyyMM'),
                        CHILDREN,
                        0
                    ) AS Child_Count,
                    IIF (
                        FORMAT(TRAVELDATE, 'yyyyMM') >= FORMAT(CAST('{$this->startDate}' AS DATE), 'yyyyMM'),
                        INFANTS,
                        0
                    ) AS Infant_Count,
                    BHD_ID
                FROM
                (
                    SELECT
                        BHD.BHD_ID,
                        BHD.TRAVELDATE,
                        CASE
                            WHEN gc.isgroup = 1 THEN
                                PAX_TW + PAX_DB + PAX_SG + PAX_TR + PAX_QD + PAX_OT +
                                CHD_TW + CHD_DB + CHD_SG + CHD_TR + CHD_QD + CHD_OT +
                                INF_TW + INF_DB + INF_SG + INF_TR + INF_QD + INF_OT
                            ELSE BSD.PAX + BSD.CHS + BSD.INF
                        END AS PAX,
                        CASE
                            WHEN gc.isgroup = 1 THEN
                                PAX_TW + PAX_DB + PAX_SG + PAX_TR + PAX_QD + PAX_OT
                            ELSE BSD.PAX
                        END AS ADULTS,
                        CASE
                            WHEN gc.isgroup = 1 THEN
                                CHD_TW + CHD_DB + CHD_SG + CHD_TR + CHD_QD + CHD_OT
                            ELSE BSD.CHS
                        END AS CHILDREN,
                        CASE
                            WHEN gc.isgroup = 1 THEN
                                INF_TW + INF_DB + INF_SG + INF_TR + INF_QD + INF_OT
                            ELSE BSD.INF
                        END AS INFANTS
                FROM BHD WITH (NOLOCK)
                    INNER JOIN (
                        SELECT DISTINCT
                            PXC.BHD_ID,
                            IIF(BRL.ROOM_TYPE = '', 1, 0) AS isgroup
                        FROM PXC WITH (NOLOCK)
                        INNER JOIN BRL WITH (NOLOCK)
                            ON BRL.PXC_ID = PXC.PXC_ID
                    ) gc ON gc.BHD_ID = BHD.BHD_ID
                    LEFT JOIN BSD WITH (NOLOCK)
                        ON BSD.BHD_ID = BHD.BHD_ID
                        AND BSD.BSL_ID = 0
                    OUTER APPLY (
                        SELECT TOP 1
                            PXC2.SEQ,
                        PAX2.PAX_TW, PAX2.PAX_DB, PAX2.PAX_SG, PAX2.PAX_TR, PAX2.PAX_QD, PAX2.PAX_OT,
                        PAX2.CHD_TW, PAX2.CHD_DB, PAX2.CHD_SG, PAX2.CHD_TR, PAX2.CHD_QD, PAX2.CHD_OT,
                        PAX2.INF_TW, PAX2.INF_DB, PAX2.INF_SG, PAX2.INF_TR, PAX2.INF_QD, PAX2.INF_OT
                        FROM PXC PXC2
                        INNER JOIN PAX PAX2
                            ON PXC2.PAX_ID = PAX2.PAX_ID
                        WHERE PXC2.BHD_ID = BHD.BHD_ID
                        AND (
                                (gc.isgroup = 1 AND PXC2.SEQ = 1)
                            OR (gc.isgroup = 0)
                            )
                        ORDER BY
                            PXC2.SEQ
                    ) PXC_PAX
                ) x
            )";
        
    }

    private function cetSegmentOrder() {
        return "BOOKINGSEGMENTS AS (
                    SELECT BHD_ID, SegmentId FROM (
                        SELECT
                            BHD.BHD_ID,
                            SegmentId,
                            ROW_NUMBER() OVER (PARTITION BY BHD.BHD_ID Order BY position) AS rowno
                        FROM BHD WITH (NOLOCK)
                            LEFT JOIN PAX_COUNT WITH (NOLOCK)
                                ON PAX_COUNT.BHD_ID = BHD.BHD_ID
                            LEFT JOIN {$this->sqlsrv_CU}.dbo.SegmentsMap
                                ON (SegmentsMap.DRMCode = BHD.AGENT OR SegmentsMap.DRMCode Is Null)
                                    And (SegmentsMap.Branch = BHD.BRANCH Or SegmentsMap.Branch Is Null)
                                    And (SegmentsMap.Department = BHD.DEPARTMENT Or SegmentsMap.Department Is Null)
                                    And (SegmentsMap.PaxFrom <= PAX_COUNT.paxcount Or SegmentsMap.PaxFrom Is Null )
                        )bkseg
                    WHERE rowno = 1
                )";
    }

    private function cetAllAcountsAK() {
        return "AllAccounts AS (
			SELECT 
				IDA_Account AS IDAccountAK,
				AccountNumber,
				Description
			FROM {$this->sqlsrv_CU}.dbo.AKAccounts WITH (NOLOCK)
                )";
    }

    private function cetNPSum() {
        return "NPSUM_TABLE AS (
                    SELECT
                        NPEntries.FullReference,
                        ISNULL(Sum(NPAmounts.Amount), 0) As NPSUM,
                        BHD.REFERENCE
                    FROM {$this->sqlsrv_CU}.dbo.NPAmounts WITH (NOLOCK)
                        INNER JOIN {$this->sqlsrv_CU}.dbo.NPEntries WITH (NOLOCK)
                            ON NPAmounts.ID_NPEntries = NPEntries.ID
                        INNER JOIN BHD WITH (NOLOCK)
                            ON BHD.REFERENCE = NPEntries.Reference
                    WHERE BHD.TRAVELDATE >= '2023-01-01'
                    Group By
                        NPEntries.FullReference,
                        BHD.REFERENCE
                )";
    }
    private function cetBookonGrossProfit() {
        return "BookoN AS (
                    SELECT --DISTINCT
                        BHD.BHD_ID,
                        BHD1.FULL_REFERENCE AS GroupBook,
                        BHD.FULL_REFERENCE AS Booking,
                        AKAccounts.Description ,     -- Nuevo campo
                        AKTPServices.IDAccountAK,
                        BHD1.TRAVELDATE,
                        ((PAX1.PAX_TWRM * 2 + PAX1.PAX_DBRM * 2 + PAX1.PAX_SGRM) + PAX1.PAX_TRRM * 3) + PAX1.PAX_QDRM * 4 AS GB_QTY,
                        BSD.PAX,
                        ROUND(
                            ISNULL(Bill.Taxed, 0) +
                            IIF(BHD.TRAVELDATE < '2023-07-01', (COALESCE(NPTOTAL.NPTOTAL, 0)) / 108 * 100, (COALESCE(NPTOTAL.NPTOTAL, 0)) / 113 * 100) +
                            ISNULL(VIATICOS.crccash_converted, 0) + ISNULL(Provision.ProvNet, 0), 2
                        ) AS TOTALCOST,
                        ROUND(
                            ISNULL(Bill.Taxed, 0) +
                            IIF(BHD.TRAVELDATE < '2023-07-01', (COALESCE(NPTOTAL.NPTOTAL, 0)) / 108 * 100, (COALESCE(NPTOTAL.NPTOTAL, 0)) / 113 * 100) +
                            ISNULL(VIATICOS.crccash_converted, 0) + ISNULL(Provision.ProvNet, 0), 2
                        ) / (((PAX1.PAX_TWRM * 2 + PAX1.PAX_DBRM * 2 + PAX1.PAX_SGRM) + PAX1.PAX_TRRM * 3) + PAX1.PAX_QDRM * 4) * BSD.PAX AS AmountFB
                    FROM BHD WITH (NOLOCK)
                        INNER JOIN BSL WITH (NOLOCK)
                            ON dbo.BSL.BHD_ID = BHD.BHD_ID
                        INNER JOIN BHD AS BHD1 WITH (NOLOCK)
                            ON BHD1.PCM_ID = BSL.COMP_PCM_ID AND BSL.DATE = BHD1.TRAVELDATE
                        INNER JOIN PKG WITH (NOLOCK)
                            ON BSL.OPT_ID = PKG.OPT_ID
                        /*INNER JOIN BSL AS BSL1 WITH (NOLOCK)
                            ON BSL1.BHD_ID = BHD1.BHD_ID*/
                        INNER JOIN PXC WITH (NOLOCK)
                            ON BHD1.BHD_ID = PXC.BHD_ID
                        INNER JOIN PAX AS PAX1 WITH (NOLOCK)
                            ON PXC.PAX_ID = PAX1.PAX_ID
                        INNER JOIN BSD WITH (NOLOCK)
                            ON BSD.BSL_ID = BSL.BSL_ID
                        INNER JOIN OPT WITH (NOLOCK)
                            ON BSL.OPT_ID = OPT.OPT_ID
                        INNER JOIN {$this->sqlsrv_CU}.dbo.AKTPServices AS AKTPServices WITH (NOLOCK) 
                            ON OPT.SERVICE = AKTPServices.IDService
                        INNER JOIN {$this->sqlsrv_CU}.dbo.AKAccounts AS AKAccounts WITH (NOLOCK)
                            ON AKTPServices.IDAccountAK = AKAccounts.IDA_Account
                        LEFT JOIN NPSUM_TABLE 
                            ON BHD1.REFERENCE = NPSUM_TABLE.REFERENCE 
                        LEFT JOIN VIATICOS 
                            ON BHD1.REFERENCE = VIATICOS.bookingref
                        LEFT JOIN USDCASH
                            ON BHD1.REFERENCE = USDCASH.bookingref
                        LEFT JOIN NPTOTAL
                            ON BHD1.REFERENCE = NPTOTAL.bookingref
                        LEFT JOIN (
                            SELECT
                                BHD_ID,
                                SUM(IIF(cnt = 1, Total, AmountTP)) AS AmountTP,
                                SUM(IIF(cnt = 1, Total, AmountTP)) AS Total,
                                SUM(ISNULL((IIF(cnt = 1, Total, AmountTP) - (IIF(cnt = 1, Total, AmountTP) / (100 + (Porcentaje * 100))) * (Porcentaje * 100)) * IIF(Type = '03', -1, 1) * SIGN(IIF(cnt = 1, Total, AmountTP)), IIF(cnt = 1, Total, AmountTP))) AS Taxed,
                                SUM(ISNULL(IIF(TRCurr = 'CRC', (IIF(cnt = 1, Total, AmountTP) / (100 + (Porcentaje * 100))) * Porcentaje * 100, IIF(cnt = 1, Total, AmountTP) * Porcentaje) * IIF(Type = '03', -1, 1) * SIGN(IIF(cnt = 1, Total, AmountTP)), 0)) AS IVA,
                                SUM(IIF(cnt = 1, Total, AmountTP) - AmountTP) AS VATCOST,
                                SUM(Porcentaje) AS 'VAT%'
                            FROM InvoiceData
                                LEFT JOIN (
                                    SELECT
                                        APPLY_REFERENCE AS ref,
                                        COUNT(TRL_ID) AS cnt
                                    FROM TRL WITH (NOLOCK)
                                    INNER JOIN TRH WITH (NOLOCK)
                                        ON TRL.TRH_ID = TRH.TRH_ID
                                    INNER JOIN BHD WITH (NOLOCK)
                                        ON SUBSTRING(TRL.TRANSACTION_ITEM, 5, LEN(TRL.TRANSACTION_ITEM)) = BHD.REFERENCE
                                    WHERE TRL.LINE_CATEGORY IN ('VAL', 'TAX')
                                        AND TRH.LEDGER = 'P'
                                        AND TRH.TRAN_TYPE < 3
                                    GROUP BY APPLY_REFERENCE
                                ) bkgs
                                    ON bkgs.ref = REFERENCE
                            GROUP BY BHD_ID
                        ) AS Bill ON BHD1.BHD_ID = Bill.BHD_ID
                        LEFT JOIN (
                                SELECT
                                    BHD_ID,
                                    SUM(Provision.Taxed) AS ProvNet
                                FROM (
                                    SELECT
                                        BHD_ID,
                                        CODE AS Provider,
                                        BSL_ID AS Voucher,
                                        SRDate AS ServiceD,
                                        AmountIVAI * 100 / (100 + TaxRate) AS Taxed
                                    FROM Provision
                                ) AS Provision
                            GROUP BY BHD_ID
                                ) AS Provision 
                            ON BHD1.BHD_ID = Provision.BHD_ID
                    WHERE BSL.SL_STATUS IN ('CF', 'RQ', 'BL', 'PA', '')
                        AND BHD.ROLL_UP_STATUS <> 'N'
                        AND BHD.STATUS NOT IN ('QX', 'PE', 'XC', 'QU', 'TE')
                        AND BHD.STATUS NOT IN ('QX', 'PE', 'XC', 'TE')
                        AND PKG.BOOK_ON = 'Y'
                        AND BSL.SOURCE = 'P'
                        AND PXC.SEQ = 1
                        AND BHD.TRAVELDATE >= '2023-01-01'
                )";
    }
    private function cetColCashBookon() {
        return "VIATICOS AS (
                    SELECT
                        BHD.BHD_ID,
                        BNKTransactions.bookingref,
                        ISNULL(
                            SUM((BNKTransactions.debit - BNKTransactions.credit) / ExchangeRates.sales) , 0
                        ) AS crccash_converted
                    FROM {$this->sqlsrv_CU}.dbo.BNKAccounts
                        INNER JOIN {$this->sqlsrv_CU}.dbo.BNKTransactions WITH (NOLOCK)
                            ON BNKTransactions.bankaccount_id = BNKAccounts.id
                        INNER JOIN BHD WITH (NOLOCK)
                            ON BHD.REFERENCE = BNKTransactions.bookingref
                        INNER JOIN {$this->sqlsrv_CU}.dbo.ExchangeRates WITH (NOLOCK)
                            ON ExchangeRates.dateFROM = BNKTransactions.date
                    WHERE BNKAccounts.id NOT IN(28, 29)
                        AND BNKAccounts.currency_id = 'CRC'
                    GROUP BY BNKTransactions.bookingref, BHD.BHD_ID
                )";
    }
    private function cetUsdCashBookon() {
        return "USDCASH AS (
                    SELECT
                        BNKTransactions.bookingref,
                    ISNULL(
						SUM(BNKTransactions.debit) - SUM(BNKTransactions.credit),
						0
					) AS USDCASH
                    FROM {$this->sqlsrv_CU}.dbo.BNKAccounts WITH (NOLOCK)
                    INNER JOIN {$this->sqlsrv_CU}.dbo.BNKTransactions WITH (NOLOCK)
                        ON BNKTransactions.bankaccount_id = BNKAccounts.id
                    INNER JOIN BHD WITH (NOLOCK)
                        ON BHD.REFERENCE = BNKTransactions.bookingref
                    WHERE BNKAccounts.id NOT IN (28, 29)
                        AND BNKAccounts.currency_id = 'USD'
                        AND BHD.TRAVELDATE >= '2023-01-01'
                    GROUP BY BNKTransactions.bookingref
                )";
    }
    private function cetNPTotal() {
        return "NPTOTAL AS (
                    SELECT
                        BHD.BHD_ID,
                        BHD.TRAVELDATE,
                        COALESCE(USDCASH.bookingref, NPSUM_TABLE.REFERENCE) AS bookingref,
                        ISNULL(NPSUM_TABLE.NPSUM, 0) + ISNULL(USDCASH.USDCASH, 0) AS NPTOTAL
                    FROM USDCASH
                    FULL OUTER JOIN NPSUM_TABLE
                        ON USDCASH.bookingref = NPSUM_TABLE.REFERENCE
                LEFT JOIN BHD 
                    ON BHD.REFERENCE = COALESCE(USDCASH.bookingref, NPSUM_TABLE.REFERENCE)
                    WHERE BHD.TRAVELDATE >= '2023-01-01'
                )";
    }

    private function cetProvisionNew() {
        return "Provision AS (
                    SELECT DISTINCT
                        BHD.BHD_ID,
                        CRM.CODE,
                        CONVERT(DATE, BHD.TRAVELDATE) AS TRAVELDATE,
                        CONVERT(DATE, BHD.LAST_SERVICE_DATE) AS DepartureDate,
                        BHD.AGENT,
                        BHD.NAME AS BookingName,
                        BHD.FULL_REFERENCE,
                        BHD.REFERENCE,
                        BSL.SL_STATUS,
                        BSL.BSL_ID,
                        BSL.COST_CURRENCY AS CCURR,
                        IIF(
                            TRL_PP.ISPP = 1,
                            TRL_PP.value,
                            IIF(
                                BSL.COST_CURRENCY = 'CRC', BSD.COST / ExchangeRates.sales,
                                BSD.COST
                            )
                        ) AS AmountIVAI,
                        BSL.VOUCHER_STATUS,
                        OPT.SUPPLIER,
                        BHD.STATUS,
                        BSL.DATE AS SRDate,
                        OPT.SERVICE,
                        ExchangeRates.sales as ExSales,
                        CAST(IIF(ISNUMERIC(CRM.UDTEXT8) = 1, CRM.UDTEXT8, '13') AS INT) AS TaxRate,
                        COALESCE(TRL_PP.ISPP, 0) AS ISPP,
                        NULL AS TRANSACTION_VALUE,
                        NULL  AS LINE_CATEGORY, --IIF(FB.Reference IS NULL, NULL, TRL_PP.LINE_CATEGORY) AS LINE_CATEGORY,
                        NULL  AS APPLY_REFERENCE, --IIF(FB.Reference IS NULL, NULL, TRL_PP.APPLY_REFERENCE) AS APPLY_REFERENCE,
                        IIF(TRL_PP.BSL_ID IS NOT NULL, 1, 0) AS HasFBData,
                        AKAccounts.AccountNumber,  -- Nuevo campo
                        AKAccounts.Description ,     -- Nuevo campo
                        AKTPServices.IDAccountAK
                    FROM BSL WITH (NOLOCK)
                        INNER JOIN BHD WITH (NOLOCK)
                            ON BSL.BHD_ID = BHD.BHD_ID
                        INNER JOIN BSD WITH (NOLOCK)
                            ON BSL.BSL_ID = BSD.BSL_ID
                        INNER JOIN OPT WITH (NOLOCK)
                            ON BSL.OPT_ID = OPT.OPT_ID
                        INNER JOIN CRM WITH (NOLOCK)
                            ON OPT.SUPPLIER = CRM.CODE
                        -- Joins to data for type of account
                        INNER JOIN {$this->sqlsrv_CU}.dbo.AKTPServices AS AKTPServices WITH (NOLOCK)
                            ON OPT.SERVICE = AKTPServices.IDService
                        INNER JOIN {$this->sqlsrv_CU}.dbo.AKAccounts AS AKAccounts WITH (NOLOCK)
                            ON AKTPServices.IDAccountAK = AKAccounts.IDA_Account
                        -- end Joins to data for type of account
                        LEFT JOIN {$this->sqlsrv_CU}.dbo.ExchangeRates
                            ON ExchangeRates.dateFROM = BHD.TRAVELDATE
                        LEFT JOIN (
                            SELECT DISTINCT
                                TRH.CODE AS COD,
                                IIF (
                                    TRL.TRANSACTION_CURRENCY = 'CRC',
                                    TRL.TRANSACTION_VALUE / er.Sales,
                                    TRL.TRANSACTION_VALUE
                                ) AS value,
                                BSL_ID,
                                APPLY_REFERENCE,
                                LINE_CATEGORY,
                                CASE
                                    WHEN APPLY_REFERENCE LIKE '%pp%'
                                    THEN 1
                                    ELSE 0
                                END AS ISPP
                                FROM TRL WITH (NOLOCK)
                                    INNER JOIN TRH WITH (NOLOCK)
                                        ON TRL.TRH_ID = TRH.TRH_ID
                                    LEFT JOIN {$this->sqlsrv_CU}.dbo.ExchangeRates er
                                        ON er.dateFROM = TRH.TRANSACTION_DATE
                                WHERE TRL.LINE_CATEGORY IN ('VAL', 'TAX')
                                    AND TRH.LEDGER = 'P'
                                    AND TRH.TRAN_TYPE < 3
                                    AND TRANSACTION_DATE > '2023-01-01'
                                GROUP BY TRANSACTION_VALUE , BSL_ID, APPLY_REFERENCE, LINE_CATEGORY, TRH.CODE, TRANSACTION_CURRENCY, er.Sales
                            ) AS TRL_PP
                                ON BSL.BSL_ID = TRL_PP.BSL_ID
                            LEFT JOIN (
                                SELECT *
                                FROM {$this->sqlsrv_CU}.dbo.CostInvoices WITH (NOLOCK)
                                --WHERE Verified = 1
                                UNION ALL
                                SELECT *
                                FROM {$this->sqlsrv_CU}.dbo.FEBills WITH (NOLOCK)
                                --WHERE Verified = 1
                            ) AS FB
                                ON TRIM(REPLACE(TRANSLATE(TRL_PP.APPLY_REFERENCE, '{$this->feStringFilter}', REPLICATE('#', LEN('{$this->feStringFilter}'))), '#', '')) = FB.Reference
                                    AND TRL_PP.COD = FB.CrmCode
                    WHERE
                        (
                            TRL_PP.ISPP = 1
                            OR
                            BSL.VOUCHER_STATUS <> 'F'
                        )
                        AND BSL.SL_STATUS NOT IN ('OP', 'WA', 'UP', 'DE', 'RH', 'CN', 'UA', 'NX', 'RQ', 'CX')
                        AND BHD.STATUS NOT IN ('QX', 'XC', 'QU', 'TE', 'CX', 'XX')
                        AND BSD.COST > 0
                        AND BHD.AGENT <> 'TEST1 '
                        AND OPT.SUPPLIER NOT IN ('MINAE', 'VIACOL', 'VIADOL', 'ARATOU', 'PRODCR', 'COOALI', 'GUIAPO', 'TROEUR')
                        AND NOT (
                            CRM.ANALYSIS_MASTER6 IN ('FC','SI','HG','GF') AND TRL_PP.ISPP = 0
                            AND BSL.VOUCHER_STATUS IN ('C')
                        )
                        AND NOT EXISTS (
                            SELECT 1
                            FROM (
                                SELECT Reference, CrmCode, Verified
                                FROM {$this->sqlsrv_CU}.dbo.CostInvoices
                                UNION ALL
                                SELECT Reference, CrmCode, Verified
                                FROM {$this->sqlsrv_CU}.dbo.FEBills
                            ) F
                            WHERE F.Verified = 1
                                AND F.CrmCode = TRL_PP.COD
                                AND  TRIM(REPLACE(TRANSLATE(TRL_PP.APPLY_REFERENCE, '{$this->feStringFilter}', REPLICATE('#', LEN('{$this->feStringFilter}'))), '#', '')) = F.Reference
                        )
                        AND BHD.TRAVELDATE >= '2023-01-01'
                        AND (
                            VOUCHER_STATUS != 'C'
                            OR
                            (VOUCHER_STATUS = 'C' AND FB.Verified = 0)
                            OR
                            (VOUCHER_STATUS = 'C' AND TRL_PP.ISPP = 1)
                        )
                )";
    }

    private function cetInvData() {
        return "InvoiceData As (
                    SELECT
                        BHD.BHD_ID,
                        TRH.CODE,
                        BHD.FULL_REFERENCE,
                        BHD.AGENT,
                        BHD.TRAVELDATE,
                        TRH.REFERENCE,
                        BHD.CURRENCY,
                        IIF (
                            TRL.TRANSACTION_CURRENCY = 'CRC',
                            TRL.TRANSACTION_VALUE / ExchangeRates.Sales,
                            TRL.TRANSACTION_VALUE
                        ) AS AmountTP,
                        COALESCE(CostInvoices.Currency, TRL.TRANSACTION_CURRENCY) AS TRCurr,
                        CostInvoices.Type as Type,
                        CostInvoices.Total AS TotalBase,
                        CostInvoices.Reference AS InvoiceNo,
                        CostInvoices.InvoiceDate,
                        CRM.ANALYSIS_MASTER6 AS FCOSI,
                        IDAccountAK,
                        IIF (
                            CostInvoices.Total IS NULL,
                            IIF (
                                TRL.TRANSACTION_CURRENCY = 'CRC',
                                TRL.TRANSACTION_VALUE / ExchangeRates.Sales,
                                TRL.TRANSACTION_VALUE
                            ),
                            IIF (
                                CostInvoices.Currency = 'CRC',
                                CostInvoices.Total / IIF (CostInvoices.ExchangeRate < 100, ExchangeRates.Sales, CostInvoices.ExchangeRate),
                                CostInvoices.Total
                            )
                        ) AS Total,
                        COALESCE(IIF(CostInvoices.Currency = 'CRC', CostInvoices.Tax /  CostInvoices.Tax / IIF(CostInvoices.ExchangeRate < 100, ExchangeRates.Sales, CostInvoices.ExchangeRate), CostInvoices.Tax), 0) As Tax,
                        COALESCE(IIF(CostInvoices.Currency = 'CRC', CostInvoices.TaxCredit / CostInvoices.Tax / IIF(CostInvoices.ExchangeRate < 100, ExchangeRates.Sales, CostInvoices.ExchangeRate), CostInvoices.TaxCredit),0) As TaxCredit,
                        ISNULL(IIF((CostInvoices.Total - CostInvoices.Tax) = 0, 0, CostInvoices.Tax / (CostInvoices.Total - CostInvoices.Tax)), 0) AS Porcentaje
                    FROM BHD WITH (NOLOCK)
                        INNER JOIN TRL WITH (NOLOCK)
                            ON SUBSTRING(TRL.TRANSACTION_ITEM, 5, LEN(TRL.TRANSACTION_ITEM)) = BHD.REFERENCE
                        INNER JOIN TRH WITH (NOLOCK)
                            ON TRL.TRH_ID = TRH.TRH_ID
                        LEFT JOIN (
                            SELECT * FROM {$this->sqlsrv_CU}.dbo.CostInvoices WITH (NOLOCK)
                            WHERE Verified = 1
                            UNION ALL
                            SELECT FB.*
                            FROM {$this->sqlsrv_CU}.dbo.FEBills FB WITH (NOLOCK)
                            WHERE Verified = 1
                        ) CostInvoices
                            ON CostInvoices.Reference = TRIM(REPLACE(TRANSLATE(TRH.REFERENCE, 
                                '{$this->feStringFilter}', 
                                REPLICATE('#', LEN('{$this->feStringFilter}'))), '#', ''))
                                AND FORMAT(CostInvoices.InvoiceDate, 'yyyy-MM') = FORMAT(TRH.TRANSACTION_DATE, 'yyyy-MM')
                                AND CostInvoices.CrmCode = TRH.CODE
                        LEFT JOIN {$this->sqlsrv_CU}.dbo.ExchangeRates WITH (NOLOCK)
							ON ExchangeRates.dateFROM = COALESCE(CostInvoices.InvoiceDate, TRH.TRANSACTION_DATE)
                        INNER JOIN CRM WITH (NOLOCK)
                            ON TRH.CODE = CRM.CODE
                        INNER JOIN BSL WITH (NOLOCK)
							ON BSL.BSL_ID = TRL.BSL_ID
						INNER JOIN OPT WITH (NOLOCK)
							ON BSL.OPT_ID = OPT.OPT_ID
						INNER JOIN {$this->sqlsrv_CU}.dbo.AKTPServices WITH (NOLOCK)
							ON OPT.SERVICE = IDService
                    WHERE TRH.TRAN_TYPE < 3
                        AND TRH.LEDGER = 'P'
                        AND TRH.REFERENCE Not Like '%PP%'
                        AND TRL.LINE_CATEGORY In ('VAL', 'TAX')
                        AND (
                            CostInvoices.Total IS NOT NULL
                            OR (
                                CRM.ANALYSIS_MASTER6 IN ('FC','SI','HG','GF')
                                AND BSL.VOUCHER_STATUS = 'C'
                            )
                        )
                        AND BHD.TRAVELDATE >= '2023-01-01'
                        -- AND (CostInvoices.Type IS NOT NULL OR (CRM.ANALYSIS_MASTER6 IS NOT NULL AND CRM.ANALYSIS_MASTER6 <> ''))
                        -- AND CostInvoices.Total Is Not Null  -- Registros con factura
                        -- AND CostInvoices.Total Is Null -- Descomenta para registros sin factura (accrued)
                )";
    }

    private function cetTPInvoice() {
        return "TPInvoice AS (
                    SELECT
                        SUM (TRL.TRANSACTION_VALUE) AS AmountTP,
                        BHD.BHD_ID,
                        SUM(CASE
                            WHEN (TRH.TRAN_TYPE = 2 AND TRH.LEDGER = 'R' AND TRL.LINE_CATEGORY <> 'COM') OR
                                (TRL.LINE_CATEGORY = 'COM' AND TRH.LEDGER = 'R' AND TRH.TRAN_TYPE <> 2)
                            THEN CASE
                                    WHEN TRL.TRANSACTION_CURRENCY = 'CRC'
                                    THEN TRL.TRANSACTION_VALUE / TRL.BASE_DIV_RATE
                                    ELSE TRL.TRANSACTION_VALUE
                                END * -1
                            ELSE CASE
                                    WHEN TRH.LEDGER = 'R'
                                    THEN TRL.TRANSACTION_VALUE
                                END
                        END) AS TPInvoiceSumme
                    FROM TRL WITH (NOLOCK)
                        INNER JOIN TRH WITH (NOLOCK)
                            ON TRL.TRH_ID = TRH.TRH_ID 
                        INNER JOIN BHD WITH (NOLOCK)
                            ON SUBSTRING(TRL.TRANSACTION_ITEM, 5, LEN(TRL.TRANSACTION_ITEM)) = BHD.REFERENCE
                    WHERE TRH.TRAN_TYPE < 3 
                        AND TRL.LINE_CATEGORY IN ('VAL', 'TAX')
                        AND BHD.AGENT NOT IN ('TEST1 ', 'ARATOU', 'PCMDEB')
                        AND BHD.STATUS <> 'LC'
                        AND BHD.TRAVELDATE >= '2023-01-01'
                    GROUP BY BHD.BHD_ID
                )";
    }
    private function cetTPSum() {
        return "TPSUM AS (
                    SELECT
                        SUM(TRL.TRANSACTION_VALUE) AS AmountTP,
                        BHD.BHD_ID
                    FROM TRL WITH (NOLOCK)
                        INNER JOIN TRH WITH (NOLOCK)
                            ON TRL.TRH_ID = TRH.TRH_ID 
                        INNER JOIN BHD WITH (NOLOCK)
                            ON SUBSTRING(TRL.TRANSACTION_ITEM, 5, LEN(TRL.TRANSACTION_ITEM)) = BHD.REFERENCE
                    WHERE TRH.TRAN_TYPE < 3 
                        AND TRH.LEDGER = 'P' 
                        AND TRH.REFERENCE Not Like '%PP%' 
                        AND TRL.LINE_CATEGORY In ('VAL', 'TAX') 
                        AND BHD.TRAVELDATE >= '2023-01-01'
                    GROUP BY BHD.BHD_ID
                )";
    }
    private function cetInvSum() {
        return "InvoiceSummary AS (
                    SELECT
                        BHD.BHD_ID AS BHDID,
                        BHD.TRAVELDATE,
                        X.DateCreated,
                        X.TotalVoucher,
                        X.TotalTaxedService,
                        X.TotalTaxes,
                        IIF(
                            CONCAT(YEAR(X.DateCreated), FORMAT(X.DateCreated, 'MM')) > CONCAT(YEAR(BHD.TRAVELDATE), FORMAT(BHD.TRAVELDATE, 'MM'))
                            AND CONCAT(YEAR(X.DateCreated), FORMAT(X.DateCreated, 'MM')) <= CONCAT(YEAR(CAST('{$this->endDate}' AS DATE)), FORMAT(CAST('{$this->endDate}' AS DATE), 'MM')),
                            1,
                            0
                        ) AS isPost
                    FROM (
                        SELECT Reference, DateCreated, TotalVoucher, TotalTaxedService, TotalTaxes
                        FROM {$this->sqlsrv_CU}.dbo.Invoices WITH (NOLOCK)
                        UNION ALL
                        SELECT Reference, DateCreated, TotalVoucher, TotalTaxedService, TotalTaxes
                        FROM {$this->sqlsrv_CU}.dbo.CreditNotes WITH (NOLOCK)
                    ) AS X
                    INNER JOIN BHD WITH (NOLOCK)
                        ON BHD.REFERENCE = X.Reference
                    WHERE CONCAT(YEAR(X.DateCreated), FORMAT(X.DateCreated, 'MM')) <= CONCAT(YEAR(BHD.TRAVELDATE), FORMAT(BHD.TRAVELDATE, 'MM'))
                    OR (
                            CONCAT(YEAR(X.DateCreated), FORMAT(X.DateCreated, 'MM')) >= CONCAT(YEAR(CAST('{$this->startDate}' AS DATE)), FORMAT(CAST('{$this->startDate}' AS DATE), 'MM'))
                            AND CONCAT(YEAR(X.DateCreated), FORMAT(X.DateCreated, 'MM')) <= CONCAT(YEAR(CAST('{$this->endDate}' AS DATE)), FORMAT(CAST('{$this->endDate}' AS DATE), 'MM'))
                        )
                )";
    }
    private function cetPaxsum() {
        return "PAX_COUNT AS (
                SELECT
                BHD.BHD_ID,
                    SUM(CASE WHEN PXN.PAX_TYPE = 'A' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN PXN.PAX_TYPE = 'C' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN PXN.PAX_TYPE = 'I' THEN 1 ELSE 0 END) AS Total_PAX
                FROM BHD WITH (NOLOCK)
                    INNER JOIN PNB WITH (NOLOCK)
                        ON BHD.BHD_ID = PNB.BHD_ID
                    INNER JOIN PXN WITH (NOLOCK)
                        ON PNB.PXN_ID = PXN.PXN_ID
                GROUP BY BHD.BHD_ID
                )";
    }
    #endregion
}
