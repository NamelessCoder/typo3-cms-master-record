<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\TemplateView;

class TcaFieldRenderer
{
    public function renderField(array $parameters)
    {
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/MasterRecord/MasterRecordSync');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($parameters['table']);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $storedRecord = $queryBuilder->select('*')->from($parameters['table'])
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($parameters['row']['uid'], \PDO::PARAM_INT)))
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if (!$storedRecord) {
            return 'Save record to continue';
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $insight = GeneralUtility::makeInstance(RecordInsight::class, $parameters['table'], $storedRecord);

        $controllerContext = $objectManager->get(ControllerContext::class);
        $uriBuilder = $objectManager->get(UriBuilder::class);

        $request = $objectManager->get(Request::class);
        $request->setControllerExtensionName('MasterRecord');
        $request->setControllerName('Sync');
        $controllerContext->setRequest($request);
        $controllerContext->setUriBuilder($uriBuilder);
        $uriBuilder->setRequest($request);

        $view = $objectManager->get(TemplateView::class);
        $view->getRenderingContext()->setControllerContext($controllerContext);
        $view->setTemplatePathAndFilename('EXT:master_record/Resources/Private/Templates/Sync.html');
        $view->assign('insight', $insight);
        $view->assign('table', $parameters['table']);
        $view->assign('uid', (int) $parameters['row']['uid']);
        return $view->render();
    }
}
