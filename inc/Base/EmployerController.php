<?php
/**
 * @package InpsydeEmployees
 */

namespace Inc\Base;

use Inc\Api\SettingsApi;
use Inc\Base\BaseController;
use Inc\Api\Callbacks\AdminCallbacks;
use Inc\Api\Callbacks\EmployerCallbacks;

class EmployerController extends BaseController
{
    public $callbacks;
    public $settings;
    public function register()
    {
        if (!$this->activated("worker_manager")) {
            return;
        }
        $this->settings = new SettingsApi();
        $this->callbacks = new EmployerCallbacks();
        add_action("init", [$this, "employer_cpt"]);
        add_action("add_meta_boxes", [$this, "add_meta_boxes"]);
        add_action("save_post", [$this, "save_meta_box"]);
        add_action("manage_employer_posts_columns", [$this, "set_custom_columns",]);
        add_action("manage_employer_posts_custom_column", [$this, "set_custom_columns_data"], 10, 2);
        add_filter("manage_edit-employer_sortable_columns", [$this, "set_custom_columns_sortable",]);
        add_action("save_post", [$this, "custom_post_type_title"]);
        add_filter('default_content', function ($post_content) {
            global $post_type;
            if ('employer' == $post_type) {
                $ime2 = "Don't need to enter any title here";
                return $ime2;
            }
            return $post_content;
        });

        add_filter('default_title', function ($title) {
            global $post_type;
            if ('employer' == $post_type) {
                $ime = "Don't need to enter any content here";
                // return date('Y-m-d');
                return $ime;
            }
            return $title;
        });

        $this->setShortcodePage();
        add_shortcode("employer-display", [$this, "employer_display"]);
        add_action("admin_enqueue_scripts", [$this, "my_admin_scripts"]);
        add_action("admin_enqueue_styles", [$this, "my_admin_styles"]);
        add_action("my-overlay-controller", [$this, "my_overlay_controller"]);
    }

    public function my_overlay_controller()
    {
        ob_start();
        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/src/scss/display.scss\" type=\"text/css\" media=\"all\" />";
        echo "<script src=\"$this->plugin_url/assets/display.min.js\"></script>";
        return ob_get_clean();
    }

    public function my_admin_scripts()
    {
        // Registers and enqueues the required javascript.
        wp_enqueue_script("media-upload");
        wp_enqueue_script("thickbox");
        wp_register_script("my-upload", WP_PLUGIN_URL . "/inpsyde-employees/assets/employer.min.js", ["jquery", "media-upload", "thickbox"]);
        wp_enqueue_script("my-upload");
        wp_enqueue_media();
        wp_register_script("meta-box-image", WP_PLUGIN_URL . "/assets/media.js", ["jquery"]);
        wp_localize_script("meta-box-image", "meta_image", ["title" => __("Choose or Upload Media", "inpsyde-employees"), "button" => __("Use this media", "inpsyde-employees"),]);
        wp_enqueue_script("meta-box-image");
    }

    public function my_admin_styles()
    {
        wp_enqueue_style("thickbox");
    }

    public function employer_display($attrs, $content = null)
    {
        ob_start();
        echo "<script src=\"$this->plugin_url/src/js/display.js\"></script>";
        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/assets/display.min.css\" type=\"text/css\" media=\"all\" />";
        require_once "$this->plugin_path/templates/display.php";

        return ob_get_clean();
    }

    public function setShortcodePage()
    {
        $subpage = [["parent_slug" => "edit.php?post_type=employer", "page_title" => __('Shortcodes', 'text_domain'), "menu_title" => __('Shortcodes', 'text_domain'), "capability" => "manage_options", "menu_slug" => "overview_employer_shortcode", "callback" => [$this->callbacks, "shortcodePage"],],];
        $this
                ->settings
                ->addSubPages($subpage)->register();
    }

    public function employer_cpt()
    {
        $labels = ["name" => __('Employees', 'inpsyde-employees'), "singular_name" => __('Employee', 'inpsyde-employees'),];
        $args = ["labels" => $labels, "has_archive" => false, "public" => true, "menu_icon" => "dashicons-businessman", "exclude_from_search" => true, "can_export" => true, "publicly_queryable" => true, "supports" => ["title", "editor", "thumbnail", "excerpt"], "show_in_rest" => true,];
        register_post_type("employer", $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box("employer_name", __('Add Employee', 'inpsyde-employees'), [$this, "render_features_box"], "employer", "advanced", "high");
    }

    public function save_meta_box($post_id)
    {
        if (!isset($_POST["overview_employer_nonce"])) {
            return $post_id;
        }
        $nonce = $_POST["overview_employer_nonce"];
        if (!wp_verify_nonce($nonce, "overview_employer")) {
            return $post_id;
        }
        if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return $post_id;
        }
        if (!current_user_can("edit_post", $post_id)) {
            return $post_id;
        }

        $data = [
            "ime" => sanitize_text_field($_POST["overview_employer_ime"]),
            "prezime" => sanitize_text_field($_POST["overview_employer_prezime"]),
            "company_role" => sanitize_text_field($_POST["overview_employer_company_role"]),
            "description" => sanitize_text_field($_POST["overview_employer_description"]),
            "image" => $_POST["overview_employer_image"],
            "github" => sanitize_text_field($_POST["overview_employer_github"]),
            "linkedin" => sanitize_text_field($_POST["overview_employer_linkedin"]),
            "xing" => sanitize_text_field($_POST["overview_employer_xing"]),
            "facebook" => sanitize_text_field($_POST["overview_employer_facebook"]),
            "name" => $_POST['overview_employer_ime'] . ' ' . $_POST['overview_employer_prezime'],
            "approved" => isset($_POST["overview_employer_approved"]) ? 1 : 0,
            "featured" => isset($_POST["overview_employer_featured"]) ? 1 : 0,];

        update_post_meta($post_id, "_overview_employer_key", $data);
    }

    public function custom_post_type_title($post_id)
    {
        global $wpdb;
        if (get_post_type($post_id) == 'employer') {
            $engine= ', '.get_post_meta($post_id, 'ime', true).'l';
            $name = get_post_meta($post_id, '_overview_employer_key', true)['name'] ?? '';
            $title = $name;
            $where = array( 'ID' => $post_id );
            $wpdb->update($wpdb->posts, array( 'post_title' => $title ), $where);
        }
    }

    public function set_custom_columns($columns)
    {
        $title = $columns["title"];
        $date = $columns["date"];
        unset($columns["title"], $columns["date"]);
        $columns["name"] = __('Employee Name', 'inpsyde-employees');
        $columns["description"] = __('Description', 'inpsyde-employees');
        $columns["image"] = __('Image', 'inpsyde-employees');
        $columns["company_role"] = __('Role', 'inpsyde-employees');
        $columns["SocialLink"] = __('Social Link', 'inpsyde-employees');
        $columns["approved"] = __('Approved', 'inpsyde-employees');
        $columns["featured"] = __('Featured', 'inpsyde-employees');
        $columns["date"] = $date;
        return $columns;
    }

    public function set_custom_columns_data($column, $post_id)
    {
        $data = get_post_meta($post_id, "_overview_employer_key", true);
        wp_localize_script('mylib', 'WPURLS', array( 'siteurl' => get_option('siteurl') ));
        $ime = isset($data["ime"]) ? $data["ime"] : "";
        $prezime = isset($data["prezime"]) ? $data["prezime"] : "";
        $name = isset($data["name"]) ? $data["name"] : "";
        $description = isset($data["description"]) ? $data["description"] : "";
        $image = isset($data["image"]) ? $data["image"] : 'eh';
        $company_role = isset($data["company_role"]) ? $data["company_role"] : "";
        $github = isset($data["github"]) ? $data["github"] : "";
        $linkedin = isset($data["linkedin"]) ? $data["linkedin"] : "";
        $xing = isset($data["xing"]) ? $data["xing"] : "";
        $facebook = isset($data["facebook"]) ? $data["facebook"] : "";
        $approved = isset($data["approved"]) && $data["approved"] === 1 ? "<strong>YES</strong>" : "NO";
        $featured = isset($data["featured"]) && $data["featured"] === 1 ? "<strong>YES</strong>" : "NO";
        switch ($column) {
            case "name":
                echo "<strong>" . $name . "</strong>";
                break;
            case "description":
                echo "<strong>" . $description . "</strong>";
                break;
            case "image":
                if (isset($data["image"])) {
                    echo '<img height="120" src="' . $image . '" alt="employer image"/>';
                } else {
                    echo '<img height="120" src="' . esc_url(plugins_url('Images/avatar.jpg', __FILE__)) . '" alt="employer image"/>';
                }
                
                // echo '<img height="40" src="' . $image . '" alt="employer image"/>';
                break;
            case "company_role":
                echo "<strong>" . $company_role . "</strong>";
                break;
            case "SocialLink":
                echo '<strong>GitHub:</strong> <a href="' . $github . '">' . $github . '</a><br/><strong>LinkedIn:</strong> <a href="' . $linkedin . '">' . $linkedin . '</a><br/><strong>XING:</strong> <a href="' . $xing . '">' . $xing . '</a><br/><strong>facebook: </strong><a href="' . $facebook . '">' . $facebook . "</a>";
                break;
            case "approved":
                echo $approved;
                break;
            case "featured":
                echo $featured;
                break;
        }
    }

    public function set_custom_columns_sortable($columns)
    {
        $columns["ime"] = "ime";
        $columns["prezime"] = "prezime";
        $columns["name"] = "name";
        $columns["description"] = "description";
        $columns["image"] = "image";
        $columns["company_role"] = "company_role";
        $columns["SocialLink"] = "SocialLink";
        $columns["approved"] = "approved";
        $columns["featured"] = "featured";
        return $columns;
    }
   
    public function render_features_box($post)
    {
        wp_nonce_field("overview_employer", "overview_employer_nonce");
        $plugindirektorij = plugins_url();
        $avatardefault = '/inpsyde-employees/inc/Base/Images/avatar.jpg';
        $defaultna = $plugindirektorij . $avatardefault;
        $data = get_post_meta($post->ID, "_overview_employer_key", true);
        $ime = isset($data["ime"]) ? $data["ime"] : "";
        $prezime = isset($data["prezime"]) ? $data["prezime"] : "";
        $company_role = isset($data["company_role"]) ? $data["company_role"] : "";
        $name = isset($data["name"]) ? $data["name"] : "";
        $description = isset($data["description"]) ? $data["description"] : "";
        $image = isset($data["image"]) ? $data["image"] : $defaultna;
        $github = isset($data["github"]) ? $data["github"] : "";
        $linkedin = isset($data["linkedin"]) ? $data["linkedin"] : "";
        $xing = isset($data["xing"]) ? $data["xing"] : "";
        $facebook = isset($data["facebook"]) ? $data["facebook"] : "";
        $approved = isset($data["approved"]) ? $data["approved"] : false;
        $featured = isset($data["featured"]) ? $data["featured"] : false; ?>
        <div class="block__container">
            <div class="about">
                <div class="employee-name">
                    <input type="text" placeholder="First Name" id="overview_employer_ime" name="overview_employer_ime" class="widefat" value="<?php echo esc_attr($ime); ?>"></p>
                    <input type="text" placeholder="Last Name" id="overview_employer_prezime" name="overview_employer_prezime" class="widefat" value="<?php echo esc_attr($prezime); ?>">
                </div>
                <div class="img-position">
                <fieldset>
                        <center>
                            <?php
                                if (isset($data["image"])) {
                                    echo '<img height="120" src="' . $image . '" alt="employer image"/>';
                                } else {
                                    echo '<img height="120" src="' . esc_url(plugins_url('Images/avatar.jpg', __FILE__)) . '" alt="employer image"/>';
                                } ?>
                            <input type="url" placeholder="Image of employee" class="large-text" name="overview_employer_image" id="overview_employer_image" value="
                            <?php
                                if (isset($data["image"])) {
                                    echo esc_attr($image);
                                } else {
                                    echo esc_attr($defaultna);
                                } ?>">
                            <p>
                                <?php
/**
 * The button that opens our media uploader
 * The "data-media-uploader-target" value should match the ID/unique selector of our field, in this case "overview_employer_image".
 * We'll use this value to dynamically inject the file URL of our uploaded media asset into your field once successful (in the myplugin-media.js file)
 */
                                ?>
                                <button type="button" class="button" id="upload_image_button" data-media-uploader-target="#overview_employer_image"><?php _e("Upload Media", "myplugin"); ?></button>
                        </center></fieldset><p>
                        <select id="overview_employer_company_role" name="overview_employer_company_role" class="widefat">
                            <option selected="selected" value="" disabled="disabled">Position</option>
                            <option value="ceo" <?php selected($company_role, "ceo"); ?>>CEO</option>
                            <option value="developer" <?php selected($company_role, "developer"); ?>>Developer</option>
                            <option value="project-manager" <?php selected($company_role, "project-manager"); ?>>Project Manager</option>
                        </select>
                </div>
            </div>
            <p>
                <textarea  type="text" placeholder="Employee short description/biography" id="overview_employer_description" name="overview_employer_description" rows="4" class="widefat"><?php echo esc_attr($description); ?></textarea>
            </p>
            <p>
                <label class="meta-label" for="overview_employer_name">Name</label>
                <input type="text" id="overview_employer_name" name="overview_employer_name" class="widefat" value="<?php echo esc_attr($name); ?>">
            </p>
            <div class="socials">
                <p>
                    <label class="meta-label" for="overview_employer_github">Github</label>
                    <input type="text" id="overview_employer_github" name="overview_employer_github" class="widefat" value="<?php echo esc_attr($github); ?>">
                    <label class="meta-label" for="overview_employer_linkedin">Linkedin</label>
                    <input type="text" id="overview_employer_linkedin" name="overview_employer_linkedin" class="widefat" value="<?php echo esc_attr($linkedin); ?>">
                    <label class="meta-label" for="overview_employer_xing">Xing</label>
                    <input type="text" id="overview_employer_xing" name="overview_employer_xing" class="widefat" value="<?php echo esc_attr($xing); ?>">
                    <label class="meta-label" for="overview_employer_facebook">Facebook</label>
                    <input type="text" id="overview_employer_facebook" name="overview_employer_facebook" class="widefat" value="<?php echo esc_attr($facebook); ?>">
                </p>
            </div>
            <div class="components-base-control__field css-11vcxb9-StyledField e1puf3u1"><span
                    class="components-checkbox-control__input-container"><input id="overview_employer_approved"
                                                                            class="components-checkbox-control__input"
                                                                            type="checkbox"
                                                                            name="overview_employer_approved"
                                                                            value="1" <?php echo $approved ? "checked" : ""; ?>
                                                                            ><svg
                                                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img"
                                                                            class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path
                                                                            d="M18.3 5.6L9.9 16.9l-4.6-3.4-.9 1.2 5.8 4.3 9.3-12.6z"></path></svg></span><label
                    class="components-checkbox-control__label" for="inspector-checkbox-control-7">Approved</label>
            </div>
            <div class="components-base-control__field css-11vcxb9-StyledField e1puf3u1"><span
                    class="components-checkbox-control__input-container"><input id="overview_employer_featured"
                                                                            class="components-checkbox-control__input"
                                                                            type="checkbox"
                                                                            name="overview_employer_featured"
                                                                            value="1" <?php echo $featured ? "checked" : ""; ?>
                                                                            ><svg
                                                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img"
                                                                            class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path
                                                                            d="M18.3 5.6L9.9 16.9l-4.6-3.4-.9 1.2 5.8 4.3 9.3-12.6z"></path></svg></span><label
                    class="components-checkbox-control__label" for="inspector-checkbox-control-7">Featured</label>
            </div>
        </div>
        <?php
    }
}