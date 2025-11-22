<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Repository;

use App\Domain\Dit\Gifts\Entity\Certificate;
use App\Domain\Dit\Gifts\Entity\CertificateUsage;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/** @extends QueryRepository<Certificate> */
class CertificatesQueryRepository extends QueryRepository
{
    protected string $entityName = Certificate::class;

    /** @return Enumerable<int, Certificate> */
    public function getCertificatesList(string $search): Enumerable
    {
        $sql = $this->getCommonGetSql();

        return $this->query(
            $sql,
            [
                'pSearch' => $search,
            ]
        );
    }

    public function getCertificate(string $contract, string $certificateNumber): Certificate
    {
        $sql = $this->getCommonGetSql();

        $list = $this->query(
            $sql,
            [
                'pSearch' => $certificateNumber,
            ]
        );

        $certificate = $list->first();

        if ($certificate === null || $certificate->number !== $certificateNumber || $certificate->partnerContract !== $contract) {
            throw new EntityNotFoundDatabaseException("сертификат с номером = $certificateNumber и контрактом $contract не найден");
        }

        return $certificate;
    }

    /**
     * @return Enumerable<int, CertificateUsage>
     */
    public function getCertificatesUsages(array $certificatesNumbers): Enumerable
    {
        $sql = "   
         SELECT
              t.id
            , t.sertificat_number
            , t.header_id
            , t.summ
            , SUM (t.summ) OVER (PARTITION BY t.sertificat_number ORDER BY t.summ_use_date RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW )  sum_remains
            , t.header
            , TO_CHAR(t.summ_use_date, 'YYYY-MM-DD HH24:MI:SS') summ_use_date
            , t.commentary 
            , t.type
            FROM ( SELECT 
                       to_char(su.id) id
                       , su.numsert sertificat_number
                       , su.header header_id
                       , -1 * su.sumuse summ
                       , CASE
                           WHEN su.header = '123' 
                             THEN 'Автосписание'
                               WHEN su.header IS NULL
                                 THEN NULL
                                   ELSE TO_CHAR(su.header)
                                     END header
                       , NVL(nvl(h.real_data, h.DATA) , sul.log_ts) summ_use_date
                       , su.commentary commentary 
                       , null type
                       FROM tehno.sertificat_use su 
                       LEFT JOIN tehno.header h ON h.id = su.header
                       LEFT JOIN tehno.sertificat_use_log sul ON sul.id = su.id AND sul.header = '123'
                       UNION ALL
                       SELECT 
                       sull.id || sull.type id
                       , sull.numsert sertificat_number
                       , NULL header_id
                       , sull.sumuse summ
                       , 'Автосписание' header
                       , sull.log_ts summ_use_date
                       , sull.commentary commentary 
                       , sull.type
                       FROM tehno.sertificat_use_log sull
                       WHERE sull.type = 'remain' AND sull.header = '123'
                       UNION ALL
                       SELECT 
                       sull.id || sull.type id
                       , sull.numsert sertificat_number
                       , NULL header_id
                       , -1 * sull.sumuse summ
                       , 'Автосписание' header
                       , sull.log_ts summ_use_date
                       , sull.commentary commentary 
                       , sull.type
                       FROM tehno.sertificat_use_log sull
                       WHERE sull.type = 'add' AND sull.header = '123'
                       AND NOT EXISTS (SELECT NULL FROM tehno.sertificat_use su WHERE su.id = sull.id)
                       UNION ALL
                       SELECT 
                       to_char(sa.id) id
                       , sa.numsert sertificat_number
                       , NULL header_id
                       , sa.add_summ summ
                       , 'Начисление' header
                       , sa.add_date summ_use_date
                       , sa.commentary commentary 
                       , null type
                       FROM tehno.sertificat_adds sa ) t
            WHERE t.sertificat_number IN (:sertificat_numbers)
            ORDER BY t.summ_use_date DESC
        ";

        $raw = $this->conn->query(
            $sql,
            [
                'sertificat_numbers' => $certificatesNumbers,
            ],
            [
                'sertificat_numbers' => ParamType::ARRAY_STRING,
            ]
        );

        return $this->customDenormalizeToCollection($raw, CertificateUsage::class);
    }

    private function getCommonGetSql(): string
    {
        return "   
            SELECT
              s.id
            , s.numsert sertificat_number
            , CASE 
                WHEN s.header = 0 OR s.header IS NULL
                  THEN NULL
                    ELSE s.header 
                      END sertificat_header_id
            , s.contract employee_contract
            , s.sumsert sertificat_summ
            -- тут схема техно
            , s.sumsert - tehno.totsclad.getsummasertuse(s.numsert) sertificat_remains
            , s.data_create sertificat_data_create
            , s.data_end sertificat_data_end 
            ----------------------------------
            , s.type_sert sertificat_type_id
            , st.name sertificat_type_name
            -----------------------------------
            , s.curr sertificat_currency_id
            , c.logo sertificat_currency_logo
            
            FROM tehno.sertificat s
            JOIN tehno.currency c ON c.currency = s.curr
            JOIN tehno.sertificat_type st ON st.id = s.type_sert     
            WHERE 1=1
            AND (s.numsert = :pSearch or (REGEXP_LIKE(:pSearch, '^\d+$') AND s.header = TO_NUMBER(:pSearch)) or s.contract = :pSearch)
            ORDER BY s.data_create DESC, s.numsert DESC
            ";
    }
}
