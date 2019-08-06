<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAnalytics\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use Magento\Config\Model\Config\Factory;

/**
 * Controller to record Admin analytics usage log
 */
class DisableAdminUsage extends Action implements HttpPostActionInterface
{
    /**
     * @var Factory
     */
    private $configFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var NotificationLogger
     */
    private $notificationLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DisableAdminUsage constructor.
     *
     * @param Action\Context           $context
     * @param ProductMetadataInterface $productMetadata
     * @param NotificationLogger       $notificationLogger
     * @param Factory                  $configFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Action\Context $context,
        ProductMetadataInterface $productMetadata,
        NotificationLogger $notificationLogger,
        Factory $configFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->configFactory = $configFactory;
        $this->productMetadata = $productMetadata;
        $this->notificationLogger = $notificationLogger;
        $this->logger = $logger;
    }

    /**
     * Changes the value of config/admin/usage/enabled
     */
    public function disableAdminUsage()
    {
        $configModel = $this->configFactory->create();
        $configModel->setDataByPath('admin/usage/enabled', 0);
        $configModel->save();
    }

    /**
     * Log information about the last admin usage selection
     *
     * @return ResultInterface
     */
    public function markUserNotified() : ResultInterface
    {
        $responseContent = [
            'success' => $this->notificationLogger->log(
                $this->productMetadata->getVersion()
            ),
            'error_message' => ''
        ];

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }

    /**
     * Log information about the last shown advertisement
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->disableAdminUsage();
        $this->markUserNotified();
    }

    /**
     * Checks if DisableAdminUsage is allowed
     *
     * @return bool
     */
    public function _isAllowed()
    {
        $isAllowed = parent::_isAllowed();
        return $isAllowed;
    }
}
