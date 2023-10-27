<?php declare(strict_types=1);

namespace YireoTraining\MagewireMiniCart\Magewire;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magewirephp\Magewire\Component;
use Yireo\CommonViewModels\ViewModel\CurrentProduct;

class AddToCart extends Component
{
    public int $productId = 0;

    private Session $checkoutSession;
    private ProductRepositoryInterface $productRepository;
    private CartInterface $cart;
    private FormKey $formKey;
    private CurrentProduct $currentProduct;

    /**
     * @param Session $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param FormKey $formKey
     * @param CurrentProduct $currentProduct
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        Session $checkoutSession,
        ProductRepositoryInterface $productRepository,
        FormKey $formKey,
        CurrentProduct $currentProduct
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->cart = $this->checkoutSession->getQuote();
        $this->formKey = $formKey;
        $this->currentProduct = $currentProduct;
    }

    public function mount(): void
    {
        $this->currentProduct->initialize();
        $this->productId = (int)$this->currentProduct->getProduct()->getId();
    }

    public function addToCart()
    {
        if (!$this->productId > 0) {
            throw new \RuntimeException('Product ID is missing');
        }

        $product = $this->productRepository->getById($this->productId);

        $params = [
            'formKey' => $this->formKey->getFormKey(),
            'qty' => 1,
        ];

        $params = new \Magento\Framework\DataObject($params);

        $this->cart->addProduct($product, $params);
        $this->cart->collectTotals();
        $this->cart->save();
        $this->checkoutSession->replaceQuote($this->cart)->unsLastRealOrderId();
    }
}
