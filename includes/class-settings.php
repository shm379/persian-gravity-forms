<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Settings extends GFAddOn {

	protected $_version;
	protected $_slug;
	protected $_path;
	protected $_full_path;
	protected $_short_title = 'گرویتی فرم پارسی';
	protected $_title = 'بسته گرویتی فرم پارسی';
	protected $_min_gravityforms_version = '1.9';

	private static $_instance = null;

	public function __construct() {

		parent::__construct();

		$this->_version   = GF_PERSIAN_VERSION;
		$this->_slug      = GF_PERSIAN_SLUG;
		$this->_path      = GF_PERSIAN_DIR . 'index.php';
		$this->_full_path = __FILE__;
	}

	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		parent::init();
	}

	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => 'تنظیمات گرویتی فرم پارسی',
				'fields' => array(
					array(
						'name'          => 'translate',
						'label'         => 'ترجمه گرویتی فرم',
						'tooltip'       => 'با فعالسازی این گزینه در صورتی که زبان سایت به صورت فارسی باشد گرویتی فرم به پارسی ترجمه خواهد شد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'address',
						'label'         => 'آدرس های ایران',
						'tooltip'       => 'با فعالسازی این گزینه استان ها و شهرهای ایران به فیلد "آدرس" اضافه خواهد شد. باید از تب "عمومی" فیلد آدرس گزینه "نوع آدرس" را بر روی "ایران" قرار دهید و سپس تیک "فعالسازی شهرهای ایران" را انتخاب کنید.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'national_id',
						'label'         => 'فیلد کد ملی',
						'tooltip'       => 'با فعالسازی این فیلد "کد ملی" به فیلدهای پیشرفته گرویتی فرم اضافه خواهد شد. این فیلد قابلیت اعتبار سنجی کد ملی و همچنین نمایش شهر صادر کننده کد ملی را هم دارد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'jalali',
						'label'         => 'شمسی ساز فیلد تاریخ',
						'tooltip'       => 'با فعالسازی این فیلد گزینه تاریخ جلالی به قابلیت های فیلد تاریخ گرویتی فرم اضافه خواهد شد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'currencies',
						'label'         => 'واحدهای پول ایران',
						'tooltip'       => 'با فعالسازی این فیلد گزینه واحدهای پول ایران (ریال - تومان - هزار ریال - هزار تومان) به واحدهای پول گرویتی فرم اضافه خواهد شد.<br><br>واحد های "هزار ریال - هزار تومان" با نسخه 2.3 درگاه های پرداخت به بالا سازگارند و برای نسخه های پایین تر درگاه های پرداخت قابل استفاده نیستند.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'rtl_admin',
						'label'         => 'راستچین سازی مدیریت',
						'tooltip'       => 'با فعالسازی این فیلد گزینه بخش هایی از محیط کار با گرویتی فرم در مدیریت که نیاز به راستچین سازی داشته باشند، راستچین خواهند شد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'font_admin',
						'label'         => 'فونت مدیریت',
						'tooltip'       => 'با توجه به جذاب بودن محیط کار با گرویتی فرم در بخش مدیریت سایت، با اضافه کردن فرم پارسی آن را جذاب تر نمایید.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => 'vazir',
						'choices'       => array(
							array( 'label' => 'بدون فونت', 'value' => '0' ),
							array( 'label' => 'یکان', 'value' => 'yekan' ),
							array( 'label' => 'وزیر', 'value' => 'vazir' ),
							array( 'label' => 'شبنم', 'value' => 'shabnam' ),
						),
					),
					array(
						'name'          => 'payments',
						'label'         => 'سازگاری با درگاه های پرداخت',
						'tooltip'       => 'در صورتی که از درگاه های پرداخت استفاده میکنید حتما باید این گزینه را فعال نمایید تا یک سری امکانات مربوط به درگاه پرداخت ایرانی به گرویتی فرم اضافه شود. (در حال حاضر با توجه به آزمایشی بودن این قسمت، باید فقط فعال باشد.)',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'disabled'      => 'disabled',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'rss_widget',
						'label'         => 'خبرخوان گرویتی فرم پارسی',
						'tooltip'       => 'با فعالسازی این فیلد گزینه ابزارک خبرخوان گرویتی فرم پارسی به داشبورد مدیریت وردپرس اضافه خواهد شد تا آخرین پست های گرویتی فرم پارسی را مشاهده کنید.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),

					array(
						'name'  => 'br',
						'label' => '',
						'type'  => 'br',
					),
					array(
						'name'    => 'other_tools',
						'label'   => '<strong>سایر تنظیمات اضافه شده</strong>',
						'tooltip' => 'سایر تنظیمات مربوط به امکانات اضافه شده توسط گرویتی فرم پارسی',
						'type'    => 'hr',
						'text'    => 'گزینه های زیر ارتباطی به بومی سازی برای ایران ندارند و به درخواست کاربران اضافه شده اند.',
					),
					array(
						'name'          => 'enable_transaction_id',
						'label'         => 'فعالسازی کد رهگیری',
						'tooltip'       => 'به صورت پیشفرض در گرویتی فرم فقط زمانیکه یک پرداخت انجام شود یک شماره تراکنش ثبت میشود<br> با فعالسازی این گزینه میتوانید حتی برای فرم هایی که به درگاه پرداخت متصل نیستند نیز کد رهگیری اختصاص دهید.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'transaction_id_title',
						'label'         => 'عنوان کد رهگیری',
						'tooltip'       => 'در صورتیکه گزینه بالا را فعال کرده اید، عنوانی که میخواهید برای کد رهگیری نمایش داده شود را وارد کنید.',
						'type'          => 'text',
						'default_value' => 'شماره تراکنش',
					),
					array(
						'name'          => 'transaction_id_mask',
						'label'         => 'الگوی کد رهگیری (قاب)',
						'tooltip'       => 'با توجه به آشنایی با مفهوم قاب (الگو) در گرویتی فرم، الگوی دلخواه خود برای تولید کد رهگیری را وارد کنید.',
						'type'          => 'text',
						'default_value' => '9999999999',
						'style'         => 'text-align:left; direction:ltr;',
						'after_input'   => $this->mask_instructions(),
					),
					array(
						'name'    => '_',
						'label'   => 'برچسب های ادغام (شورتکد)',
						'type'    => 'hr',
						'tooltip' => 'برچسب های ادغام (Merge Tags) در واقع همان شورتکد های گرویتی فرم هستند',
					),
					array(
						'name'          => 'add_merge_tags',
						'label'         => 'برچسب های ادغام جدید',
						'tooltip'       => 'با فعالسازی این گزینه، شورتکدهای جدیدی به لیست آنها اضافه خواهد شد. شورتکدهایی نظیر اطلاعات پرداخت - وضعیت پرداخت - کد رهگیری و ...',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'post_content_merge_tags',
						'label'         => 'برچسب ها در برگه ها',
						'tooltip'       => 'به صورت پیشفرض وقتی نوع تاییدیه ها را روی "برگه" ست میکنید امکان استفاده از "برچسب ها (شورتکد های گرویتی فرم)" در آن برگه ها وجود نخواهد داشت. با فعالسازی این گزینه این امکان فراهم خواهد شد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'entry_time',
						'label'         => 'ماندگاری برچسب ها در برگه',
						'tooltip'       => 'با فعالسازی گزینه بالا، زمانیکه کاربر به برگه تاییدیه هدایت میشود، آیدی پیام ورودی اش نیز در آدرس بار پاس داده میشود و این سبب میشود که افراد دیگر با دانستن شماره پیام (و یا آزمون و خطا) به اطلاعات آن تاییدیه دسترسی داشته پیدا کنند. برای جلوگیری از این قضیه یک مدت زمان بر حسب دقیقه وارد کنید تا پس از گذشت آن زمان دسترسی به تاییدیه منقضی شود.',
						'type'          => 'text',
						'class'         => 'small',
						'input_type'    => 'number',
						'default_value' => '0',
						'style'         => 'width:70px;',
						'after_input'   => '   (دقیقه) - در صورتی که این فیلد را خالی و یا برابر 0 قرار دهید، شماره پیام ورودی به صورت "انکریپت شده" پاس داده خواهد شد.',
					),
					array(
						'name'          => 'pre_submission_merge_tags',
						'label'         => 'برچسب ها در فیلد HTML',
						'tooltip'       => 'توسط این امکان میتوانید برای فرم های خود یک "پیشفاکتور" بسازید.<br>' . '<a target="_blank" href="http://gravityforms.ir/5690">برای مشاهده راهنما کلیک کنید.</a>',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'    => '__',
						'label'   => 'تنظیمات سایر ویژگی ها',
						'type'    => 'hr',
						'tooltip' => 'تنظیمات سایر ویژگی ها',
					),
					array(
						'name'          => 'hide_lic',
						'label'         => 'حذف بنر فعال نبودن لایسنس',
						'tooltip'       => 'در برخی نسخه های گرویتی فرم، در صورتی که لایسنس گرویتی فرم شما فعال نباشد یک بنر بالای صفحات گرویتی فرم مبنی بر عدم فعال بودن لایسنس شما ظاهر میشود که با فعالسازی این گزینه میتوانید آن را مخفی نمایید.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '0',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'live_preview',
						'label'         => 'پیشنمایش زنده',
						'tooltip'       => 'با فعالسازی این گزینه، منوی پیشنمایش زنده در ویرایشگر فرم اضافه میشود تا فرم را داخل فرانت اند سایت مشاهده نمایید. البته این گزینه ممکن است با برخی قالب ها سازگاری نداشته باشد. چون یک پست تایپ مجازی ایجاد میکند.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'label_visibility',
						'label'         => 'مدیریت لیبل فیلدها',
						'tooltip'       => 'با فعالسازی این گزینه، یک آپشن جدید در تب "نمایش" فیلدها زیر "نگه دارنده متن (Placeholder)" تحت عنوان "نمایش برچسب فیلد" اضافه خواهد شد تا بتوانید "لیبل (Lable)" فیلد ها را مخفی کنید.این گزینه برای زمانی که از "نگه دارنده متن (Placeholder)" استفاده میکنید مفید است.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'newsletter',
						'label'         => 'رویداد خبرنامه در اعلان ها',
						'tooltip'       => 'توسط این امکان میتوانید گرویتی فرم خود را به یک پلاگین خبرنامه تبدیل کنید.<br>' . '<a target="_blank" href="http://gravityforms.ir/3940">برای مشاهده توضیحات کلیک کنید.</a>',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'private_post',
						'label'         => 'وضعیت پست خصوصی',
						'tooltip'       => 'به صورت پیشفرض وقتی از "فیلدهای ارسال نوشته" استفاده کنید وضعیت پست "خصوصی" در لیست وضیعت های پست وجود ندارد که با فعالسازی این گزینه اضافه خواهد شد.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'multipage_nav',
						'label'         => 'پیمایش فرم های مرحله ای',
						'tooltip'       => 'با فعالسازی این گزینه در فرم های چند مرحله ای (چند برگه ای) در صورتی که از تب "عمومی" برگه "نشانگر پیشرفت" را روی حالت "مرحله ها" قرار دهید،‌ کاربران بدون استفاده از دکمه های "قبلی" و "بعدی" میتوانند از طریق خود "نشانگر پیشرفت" بین صفحات (مرحله ها) جابجا شوند.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'بله', 'value' => '1' ),
							array( 'label' => 'خیر', 'value' => '0' ),
						),
					),
					array(
						'name'          => 'multipage_nav_last',
						'label'         => 'شرط پیمایش فرم های مرحله ای',
						'tooltip'       => 'در صورتی که گزینه بالا را فعال کرده اید، میتوانید مشخص کنید که پیمایش بین مرحله ها همواره از ابتدا فعال باشد یا فقط زمانیکه که کاربر مرحله ها را پشت سر گذاشت و به مرحله آخر رسید فعال شود.',
						'type'          => 'radio',
						'horizontal'    => true,
						'default_value' => '1',
						'choices'       => array(
							array( 'label' => 'همواره فعال باشد', 'value' => '0' ),
							array(
								'label' => 'فقط زمانیکه تمام مراحل طی شدند و به مرحله آخر رسید فعال شود',
								'value' => '1'
							),
						),
					),
				)
			)
		);
	}


	public function settings_br( $field, $echo = true ) {

		$output = '<br>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	public function settings_hr( $field, $echo = true ) {

		$output = '';

		if ( ! empty( $field['text'] ) ) {
			$output .= $field['text'];
		}

		$output .= '<hr>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	private function mask_instructions() {

		ob_start(); ?>

        <div id="custom_mask_instructions" style="display:none;">
            <div class="custom_mask_instructions">
                <h4><?php esc_html_e( 'Usage', 'gravityforms' ) ?></h4>
                <ul class="description-list">
                    <li><?php esc_html_e( "Use a '9' to indicate a numerical character.", 'gravityforms' ) ?></li>
                    <li><?php esc_html_e( "Use a lower case 'a' to indicate an alphabetical character.", 'gravityforms' ) ?></li>
                    <li><?php esc_html_e( "Use an asterisk '*' to indicate any alphanumeric character.", 'gravityforms' ) ?></li>
                    <li><?php esc_html_e( 'All other characters are literal values and will be displayed automatically.', 'gravityforms' ) ?></li>
                </ul>

                <h4><?php esc_html_e( 'Examples', 'gravityforms' ) ?></h4>
                <ul class="examples-list">

                    <li>
                        <h5><?php esc_html_e( 'Social Security Number', 'gravityforms' ) ?></h5>
                        <span class="label"><?php esc_html_e( 'Mask', 'gravityforms' ) ?></span>
                        <code>999-99-9999</code><br/>
                        <span
                                class="label">نمونه خروجی</span>
                        <code>987-65-4329</code>
                    </li>
                    <li>
                        <h5><?php esc_html_e( 'Course Code', 'gravityforms' ) ?></h5>
                        <span class="label"><?php esc_html_e( 'Mask', 'gravityforms' ) ?></span>
                        <code>aaa 999</code><br/>
                        <span
                                class="label">نمونه خروجی</span>
                        <code>BIO 101</code>
                    </li>
                    <li>
                        <h5><?php esc_html_e( 'License Key', 'gravityforms' ) ?></h5>
                        <span class="label"><?php esc_html_e( 'Mask', 'gravityforms' ) ?></span>
                        <code>***-***-***</code><br/>
                        <span
                                class="label">نمونه خروجی</span>
                        <code>a9a-f0c-28Q</code>
                    </li>
                </ul>

            </div>
        </div>
        (<a href="javascript:void(0);" style="text-decoration: none !important;"
            onclick="tb_show('<?php echo esc_js( __( 'Custom Mask Instructions', 'gravityforms' ) ); ?>', '#TB_inline?width=350&amp;inlineId=custom_mask_instructions', '');"
            onkeypress="tb_show('<?php echo esc_js( __( 'Custom Mask Instructions', 'gravityforms' ) ); ?>', '#TB_inline?width=350&amp;inlineId=custom_mask_instructions', '');">
            راهنمای ایجاد الگو
        </a>)
		<?php
		return ob_get_clean();
	}

}