<?php

/**
 * @name
 * @desc 获取文章数据
 * @author Jakin
 */
class ArticleModel
{

    private $prefix;  //表前缀

    public function __construct()
    {

        $this->_db = Yaf_Registry::get('_db');
        $this->prefix = 'wp';
    }

    //获取单个文章内容
    public function get($id)
    {

        return $this->_db->get($this->prefix . '_posts', '*', ['ID' => $id]);
    }

    // 获取文章列表
    public function getList($wheres = '', $start = 0, $limit = 10, $option = "_posts.ID DESC", $taxonomy = "category")
    {

        $columns = [
            $this->prefix . '_posts.ID',
            $this->prefix . '_posts.post_title',
            $this->prefix . '_posts.post_content',
            $this->prefix . '_posts.post_date',
            // $this->prefix.'_terms.*',
            $this->prefix . '_term_taxonomy.taxonomy',
            $this->prefix . '_term_taxonomy.description',
            $this->prefix . '_terms.slug',

        ];

        $join = [
            "[>]{$this->prefix}_terms" => ["term_taxonomy_id" => "term_id"],
            "[>]{$this->prefix}_term_taxonomy" => ["term_taxonomy_id" => "term_taxonomy_id"],
            "[>]{$this->prefix}_posts" => ["object_id" => "ID"]
        ];

        $where = [
            "AND" => [
                $this->prefix . "_term_taxonomy.taxonomy" => $taxonomy,
                $this->prefix . "_posts.post_type" => 'post',
                $this->prefix . "_posts.post_status" => 'publish'
            ],
            "ORDER" => $this->prefix . $option,
            "LIMIT" => [$start, $limit]
        ];
        if (!empty($wheres)) {
            foreach ($wheres as $key => $val) {
                $key = $this->prefix . $key;
                $where['AND'][$key] = $val;
            }
        }
        return $this->_db->select($this->prefix . '_term_relationships', $join, $columns, $where);
    }

    public function getCount($wheres = null, $taxonomy = "category")
    {

        $columns = '*';
        $join = [
            "[>]{$this->prefix}_terms" => ["term_taxonomy_id" => "term_id"],
            "[>]{$this->prefix}_term_taxonomy" => ["term_taxonomy_id" => "term_taxonomy_id"],
            "[>]{$this->prefix}_posts" => ["object_id" => "ID"]
        ];
        $where = [
            "AND" => [
                $this->prefix . "_term_taxonomy.taxonomy" => $taxonomy,
                $this->prefix . "_posts.post_type" => 'post',
                $this->prefix . "_posts.post_status" => 'publish'
            ]
        ];
        if (!empty($wheres)) {
            foreach ($wheres as $key => $val) {
                $key = $this->prefix . $key;
                $where['AND'][$key] = $val;
            }
        }
        return $this->_db->count($this->prefix . '_term_relationships', $join, $columns, $where);

    }

    //获取特色图片
    public function getOptionsImage($id)
    {

        $join = [
            "[>]{$this->prefix}_postmeta" => ["ID" => "meta_value"]
        ];
        $where = [
            "AND" => [
                $this->prefix . "_postmeta.post_id" => $id,
                $this->prefix . "_postmeta.meta_key" => '_thumbnail_id'
            ]
        ];
        $data = $this->_db->get($this->prefix . '_posts', $join, '*', $where);
        if (!empty($data)) {
            $join = [
                "[>]{$this->prefix}_postmeta" => ["ID" => "post_id"]
            ];
            $where = [
                "AND" => [
                    $this->prefix . "_posts.ID" => $data['ID'],
                    $this->prefix . "_postmeta.meta_key" => '_wp_attached_file'
                ]
            ];
            $thumbnail = $this->_db->get($this->prefix . '_posts', $join, '*', $where);
            if (!empty($thumbnail)) {
                $data['guid'] = "/wp-content/uploads/" . $thumbnail['meta_value'];
            }
        }
        return $data;
    }

    // 获取标签
    public function getTag($limit)
    {

        $join = [
            "[>]{$this->prefix}_term_taxonomy" => ["term_id" => "term_taxonomy_id"]
        ];
        $where = [
            "AND" => [
                $this->prefix . "_term_taxonomy.taxonomy" => 'post_tag',
            ],
            "LIMIT" => $limit
        ];
        return $this->_db->select($this->prefix . '_terms', $join, '*', $where);
    }

    // 获取分类
    public function getClass($slug)
    {

        $join = [
            "[>]{$this->prefix}_term_taxonomy" => ["term_id" => "term_id"]
        ];
        $where = [
            "AND" => [
                $this->prefix . "_terms.slug" => $slug,
            ]
        ];
        $data = $this->_db->get($this->prefix . '_terms', $join, '*', $where);
        if ($data['parent'] > 0) {
            $where = [
                "AND" => [
                    $this->prefix . "_terms.term_id" => $data['parent'],
                ]
            ];
            $fData = $this->_db->get($this->prefix . '_terms', $join, '*', $where);
            $data['f_name'] = $fData['name'];
            $data['f_slug'] = $fData['slug'];
            $data['f_term_id'] = $fData['term_id'];

        }
        return $data;
    }

    public function getTerm($postId)
    {

        $join = [
            "[>]{$this->prefix}_terms" => ["term_taxonomy_id" => "term_id"],
            "[>]{$this->prefix}_term_taxonomy" => ["term_taxonomy_id" => "term_taxonomy_id"]
        ];
        $where = [
            "AND" => [
                $this->prefix . "_term_relationships.object_id" => $postId,
            ]
        ];
        return $this->_db->select($this->prefix . '_term_relationships', $join, '*', $where);
    }

    // 获取置顶文章
    public function  getStickyPosts()
    {

        $data = $this->_db->get($this->prefix . '_options', '*', ['option_name' => 'sticky_posts']);
        if (!empty($data['option_value'])) {
            $postIdArr = unserialize($data['option_value']);
            $postData = $this->getList(array("_posts.ID" => $postIdArr));
            return $postData;
        } else {
            return array();
        }
    }

}