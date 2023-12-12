<?php declare(strict_types=1);

namespace YireoTraining\MagewireMiniCart\Magewire;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\Data\CartItem;
use Magewirephp\Magewire\Component;

class MiniCart extends Component
{
    /**
     * @var CartItem[] $cartItems
     */
    public array $cartItems = [];

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
        $this->setCartItems();
    }

    public function changeCartItemQty(int $cartItemId, float $qty)
    {
        $buyRequest = new DataObject(['qty' => $qty]);
        $this->cart->updateItem($cartItemId, $buyRequest);
        $this->cart->collectTotals();
        $this->cart->save();
        $this->setCartItems();
    }

    public function decrementCartItemQty(float $cartItemId)
    {
        $cartItem = $this->cart->getItemById($cartItemId);
        $this->changeCartItemQty((int)$cartItemId, $cartItem->getQty() - 1);
    }

    public function incrementCartItemQty(float $cartItemId)
    {
        $cartItem = $this->cart->getItemById($cartItemId);
        $this->changeCartItemQty((int)$cartItemId, $cartItem->getQty() + 1);
    }

    private function setCartItems()
    {
        if ($this->cart->getItems()) {
            $this->cartItems = $this->cart->getItems();
        }
    }
}
