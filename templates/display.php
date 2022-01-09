<?php

$a = extract(shortcode_atts(array(
    'idi' => '7',
                ), $attrs));
$id_array = explode(',', $idi);
if ($idi == '7') {
    $args = array(
        'post_type' => 'employer',
        'post_status' => 'publish',
        'posts_per_page' => 7,
        'meta_query' => array(
            array(
                'key' => '_overview_employer_key',
                'value' => 's:8:"approved";i:1;s:8:"featured";i:1;',
                'compare' => 'LIKE'
            )
        )
    );
} else {
    //Do other things
    $args = array(
        'post_type' => 'employer',
        'post_status' => 'publish',
        'posts_per_page' => 7,
        'post__in' => $id_array,
        'meta_query' => array(
            array(
                'key' => '_overview_employer_key',
                'value' => 's:8:"approved";i:1;s:8:"featured";i:1;',
                'compare' => 'LIKE'
            )
        )
    );
}
$query = new WP_Query($args);
if ($query->have_posts()) :
    echo '<div class"wrapper"><div id="workers"><table >';
    echo '<tr>
    <th>NAME</th>
    <th>IMAGE</th>
    <th>POSITION</th>
    <th>MORE INFO</th>
  </tr>';
    while ($query->have_posts()) : $query->the_post();
        // echo $query->post->ID;
        $ime = get_post_meta(get_the_ID(), '_overview_employer_key', true)['ime'] ?? '';
        $prezime = get_post_meta(get_the_ID(), '_overview_employer_key', true)['prezime'] ?? '';
        $name = get_post_meta(get_the_ID(), '_overview_employer_key', true)['name'] ?? '';
        $naziv = get_post_meta(get_the_ID(), '_overview_employer_key', true)['naziv'] ?? '';
        $image = get_post_meta(get_the_ID(), '_overview_employer_key', true)['image'] ?? '';
        $company_role = get_post_meta(get_the_ID(), '_overview_employer_key', true)['company_role'] ?? '';
        $description = get_post_meta(get_the_ID(), '_overview_employer_key', true)['description'] ?? '';
        $Linkedin = get_post_meta(get_the_ID(), '_overview_employer_key', true)['linkedin'] ?? '';
        $github = get_post_meta(get_the_ID(), '_overview_employer_key', true)['github'] ?? '';
        $xing = get_post_meta(get_the_ID(), '_overview_employer_key', true)['xing'] ?? '';
        $facebook = get_post_meta(get_the_ID(), '_overview_employer_key', true)['facebook'] ?? '';
        // echo '<tr class="ov-worker--table" ><td>'.$naziv.' '.$ime.' '.$prezime.'</td><br/>
        // echo "<script> window.onload = function() {
        //     yourJavascriptFunction(param1, param2);
        // }; </script>";
        echo '<tr class="ov-worker--table" >
        <td>'.$naziv.' '.$ime.' '.$prezime.'</td><br/>
        <td><img src="' . $image . '"/></td><br/>
        <td>' . $company_role . '</td><br/>
        <td><button type="submit"  onclick="populateOverlay(
            \'<strong>NAME:</strong> ' . $name . '\',\'<strong>DESCRIPTION:</strong> ' . $description . '\',\'<strong>GITHUB:</strong> <a href=https://' . $github . '>' . $github . '</a>\',
            \'<strong>LINKEDIN:</strong> <a href=https://' . $Linkedin . '>' . $Linkedin . '</a>\',
            \'<strong>XING:</strong> <a href=https://' . $xing . '>' . $xing . '</a>\',
            \'<strong>FACEBOOK:</strong> <a href=https://'.$facebook.'>'.$facebook.'</a>\<br/><br/><a onclick=off()>Close overlay</a>  \')">More Info!</button></td></tr>';
    endwhile;
    echo '</table></div>
    <div id="info"></div>
   </div>';
endif;
wp_reset_postdata();