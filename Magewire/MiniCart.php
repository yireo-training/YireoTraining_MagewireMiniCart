<?php declare(strict_types=1);

namespace YireoTraining\MagewireMiniCart\Magewire;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magewirephp\Magewire\Component;

class MiniCart extends Component
{
    public int $cartItemsCount = 0;

    private Session $checkoutSession;
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CartInterface $cart;
    private FormKey $formKey;

    /**
     * @param Session $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FormKey $formKey
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        Session $checkoutSession,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FormKey $formKey
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cart = $this->checkoutSession->getQuote();
        $this->formKey = $formKey;
    }

    public function mount()
    {
        $this->setCartItemsCount();
    }

    public function addToCart()
    {
        $this->searchCriteriaBuilder->setPageSize(20);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchItems = $this->productRepository->getList($searchCriteria);

        /** @var ProductInterface $product */
        $products = $searchItems->getItems();
        shuffle($products);
        $product = array_pop($products);

        $params = [
            'formKey' => $this->formKey->getFormKey(),
            'qty' => 1,
        ];

        $params = new \Magento\Framework\DataObject($params);

        $this->cart->addProduct($product, $params);
        $this->cart->collectTotals();
        $this->cart->save();
        $this->checkoutSession->replaceQuote($this->cart)->unsLastRealOrderId();

        $this->setCartItemsCount();
    }

    private function setCartItemsCount()
    {
        $count = 0;
        foreach ($this->cart->getAllItems() as $cartItem) {
            $count += $cartItem->getQty();
        }

        $this->cartItemsCount = (int)$count;
    }
}
