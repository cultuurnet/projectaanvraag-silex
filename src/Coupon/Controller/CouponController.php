<?php

namespace CultuurNet\ProjectAanvraag\Coupon\Controller;

use CultuurNet\ProjectAanvraag\Coupon\CouponImporter;
use CultuurNet\ProjectAanvraag\Voter\ImportVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Controller for coupon management.
 */
class CouponController
{

    /**
     * @var CouponImporter
     */
    protected $couponImporter;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    public function __construct(CouponImporter $couponImporter, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->couponImporter = $couponImporter;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Import the coupons if uploaded.
     */
    public function importCoupons(Request $request)
    {

        if (!$this->authorizationChecker->isGranted(ImportVoter::IMPORT)) {
            throw new AccessDeniedHttpException();
        }

        $output = '';
        if ($request->files->has('coupons')) {
            $totalImported = $this->couponImporter->import($request->files->get('coupons'));
            $output .= '<p>' . $totalImported . 'new coupons were imported.</p>';
        }

        // Simple form, needed only 1 time. Use own html instead of introducing the form builder as dependency.
        $output .= '<form action="' . $request->getUri() . '" method="post" enctype="multipart/form-data">
            <label for="coupons">Select file to upload:</label>
            <input type="file" name="coupons" id="coupons">
            <input type="submit" value="Import" name="submit">
        </form>';

        return new Response($output);
    }
}
