<?php
namespace SuiteMapper\Hook;

class CategoryProviderHook implements Hook
{
    const ENDPOINT = 'https://api-gateway.remind.me/provider/categoryProvider/';

    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array $data
     */
    public function execute(array &$data)
    {
        if (!isset($data['categoryProviderId']) || empty($data['categoryProviderId'])) {
            return;
        }

        if (!isset($data['categoryProviderName']) || empty($data['categoryProviderName']) ||
            !isset($data['currentProviderName']) || empty($data['currentProviderName'])) {
            $sth = $this->pdo->prepare(
                "SELECT company_company_name, company_external_id, date_modified FROM cp_categoryprovider WHERE id = :id"
            );
            $sth->execute(['id' => $data['categoryProviderId']]);
            $provider = $sth->fetch(\PDO::FETCH_ASSOC);

            if (empty($provider['company_company_name'])) {
                $url = self::ENDPOINT . $data['categoryProviderId'];
                $json = file_get_contents($url);

                if (!empty($json)) {
                    $res = json_decode($json, true);
                }

                if (!empty($res)) {
                    $sth = $this->pdo->prepare(
                        "INSERT INTO cp_categoryprovider(
                            id, category_provider_id, date_entered, date_modified, category_id,
                            category_created, category_lastmodified, category_category_type,
                            category_category_name, category_slug, category_icon,
                            category_sort_priority, category_description, company_id,
                            company_created, company_lastmodified, company_company_name,
                            company_external_id, company_company_source, company_slug,
                            company_externallogourl, company_imagepath, company_blacklisted,
                            default_notice_period, active, sort_priority, disabled
                        ) VALUES (
                            :id, :category_provider_id, :created, :lastModified, :category_id,
                            :category_created, :category_lastModified, :category_categoryType,
                            :category_category_name, :category_slug, :category_icon,
                            :category_sort_priority, :category_description, :company_id,
                            :company_created, :company_lastModified, :company_company_name,
                            :company_external_id, :company_company_source, :company_slug,
                            :company_externalLogoUrl, :company_imagePath, :company_blacklisted,
                            :default_notice_period, :active, :sort_priority, :disabled
                        )"
                    );

                    $sth->execute([
                        'id' => $res['id'],
                        'category_provider_id' => $res['id'],
                        'created' => $res['created'],
                        'lastModified' => $res['lastModified'],
                        'category_id' => $res['category']['id'],
                        'category_created' => $res['category']['created'],
                        'category_lastModified' => $res['category']['lastModified'],
                        'category_categoryType' => $res['category']['categoryType'],
                        'category_category_name' => $res['category']['categoryName'],
                        'category_slug' => $res['category']['slug'],
                        'category_icon' => $res['category']['icon'],
                        'category_sort_priority' => $res['category']['sortPriority'],
                        'category_description' => $res['category']['description'],
                        'company_id' => $res['company']['id'],
                        'company_created' => $res['company']['created'],
                        'company_lastModified' => $res['company']['lastModified'],
                        'company_company_name' => $res['company']['companyName'],
                        'company_external_id' => $res['company']['externalId'],
                        'company_company_source' => $res['company']['companySource'],
                        'company_slug' => $res['company']['slug'],
                        'company_externalLogoUrl' => $res['company']['externalLogoUrl'],
                        'company_imagePath' => $res['company']['imagePath'],
                        'company_blacklisted' => $res['company']['blacklisted'],
                        'default_notice_period' => $res['defaultNoticePeriod'],
                        'active' => $res['active'],
                        'sort_priority' => $res['sortPriority'],
                        'disabled' => $res['disabled']
                    ]);

                    $data['categoryProviderName'] = $res['company']['companyName'];
                    $data['currentProviderName'] = $res['company']['companyName'];
                    $data['currentProvider'] = $res['company']['externalId'];
                }
            } else {
                if ((strtotime($provider['date_modified']) + 3600) < time()) {
                    $url = self::ENDPOINT . $data['categoryProviderId'];
                    $json = file_get_contents($url);

                    if (!empty($json)) {
                        $res = json_decode($json, true);
                    }

                    if (!empty($res)) {
                        $sth = $this->pdo->prepare(
                            "UPDATE cp_categoryprovider SET 
                                category_provider_id = :category_provider_id,
                                date_entered = :created,
                                date_modified = :lastModified,
                                category_id = :category_id, 
                                category_created = :category_created, 
                                category_lastmodified = :category_lastModified, 
                                category_category_type = :category_categoryType, 
                                category_category_name = :category_category_name, 
                                category_slug = :category_slug, 
                                category_icon = :category_icon, 
                                category_sort_priority = :category_sort_priority, 
                                category_description = :category_description, 
                                company_id = :company_id, 
                                company_created = :company_created, 
                                company_lastmodified = :company_lastModified, 
                                company_company_name = :company_company_name, 
                                company_external_id = :company_external_id, 
                                company_company_source = :company_company_source, 
                                company_slug = :company_slug, 
                                company_externallogourl = :company_externalLogoUrl, 
                                company_imagepath = :company_imagePath, 
                                company_blacklisted = :company_blacklisted, 
                                default_notice_period = :default_notice_period, 
                                active = :active, 
                                sort_priority = :sort_priority, 
                                disabled = :disabled 
                            WHERE id = :id"
                        );

                        $sth->execute([
                            'id' => $res['id'],
                            'category_provider_id' => $res['id'],
                            'created' => $res['created'],
                            'lastModified' => date('Y-m-d H:i:s'),
                            'category_id' => $res['category']['id'],
                            'category_created' => $res['category']['created'],
                            'category_lastModified' => $res['category']['lastModified'],
                            'category_categoryType' => $res['category']['categoryType'],
                            'category_category_name' => $res['category']['categoryName'],
                            'category_slug' => $res['category']['slug'],
                            'category_icon' => $res['category']['icon'],
                            'category_sort_priority' => $res['category']['sortPriority'],
                            'category_description' => $res['category']['description'],
                            'company_id' => $res['company']['id'],
                            'company_created' => $res['company']['created'],
                            'company_lastModified' => $res['company']['lastModified'],
                            'company_company_name' => $res['company']['companyName'],
                            'company_external_id' => $res['company']['externalId'],
                            'company_company_source' => $res['company']['companySource'],
                            'company_slug' => $res['company']['slug'],
                            'company_externalLogoUrl' => $res['company']['externalLogoUrl'],
                            'company_imagePath' => $res['company']['imagePath'],
                            'company_blacklisted' => $res['company']['blacklisted'],
                            'default_notice_period' => $res['defaultNoticePeriod'],
                            'active' => $res['active'],
                            'sort_priority' => $res['sortPriority'],
                            'disabled' => $res['disabled']
                        ]);

                        $provider['company_company_name'] = $res['company']['companyName'];
                        $provider['company_external_id'] = $res['company']['externalId'];
                    }
                }

                $data['categoryProviderName'] = $provider['company_company_name'];
                $data['currentProviderName'] = $provider['company_company_name'];
                $data['currentProvider'] = $provider['company_external_id'];
            }
        }


    }

    /**
     * @return string
     */
    public function getSyncType()
    {
        return 'change-services';
    }

    /**
     * @return string
     */
    public function getExecType()
    {
        return HookRegistry::EXEC_TYPE_PRE;
    }
}